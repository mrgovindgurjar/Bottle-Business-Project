<?php

namespace App\Services;

use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\IssueCategory;
use App\Models\IssueComment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueService
{
    public function create(array $data, $user): Issue
    {
        return DB::transaction(function () use ($data, $user) {
            $data['issue_number'] = $this->nextNumber();
            $data['created_by'] = $user->id;
            $data['reported_at'] = $data['reported_at'] ?? now();
            $data['status'] = $data['status'] ?? 'open';
            $data['priority'] = $data['priority'] ?? 'normal';
            $data['attachments'] = $this->storeFiles($data['attachment_files'] ?? null, []);
            unset($data['attachment_files']);
            $issue = Issue::create($data);
            $this->activity($issue, $user, 'created', null, $issue->status, null, $issue->assigned_to, 'Issue created');
            return $issue->fresh(['category','customer','assignee']);
        });
    }

    public function update(Issue $issue, array $data, $user): Issue
    {
        return DB::transaction(function () use ($issue,$data,$user) {
            $oldStatus = $issue->status; $oldAssignee = $issue->assigned_to;
            $existing = $issue->attachments ?? [];
            $data['attachments'] = $this->storeFiles($data['attachment_files'] ?? null, $existing);
            unset($data['attachment_files']);
            $data['updated_by'] = $user->id;
            if (($data['status'] ?? $issue->status) === 'resolved' && !$issue->resolved_at) $data['resolved_at'] = now();
            if (($data['status'] ?? $issue->status) === 'closed' && !$issue->closed_at) { $data['closed_at'] = now(); $data['closed_by'] = $user->id; }
            $issue->update($data);
            if ($oldStatus !== $issue->status || $oldAssignee !== $issue->assigned_to) {
                $this->activity($issue,$user,'updated',$oldStatus,$issue->status,$oldAssignee,$issue->assigned_to,'Issue updated');
            }
            return $issue->fresh(['category','customer','assignee']);
        });
    }

    public function changeStatus(Issue $issue, string $status, $user, ?string $message = null): Issue
    {
        return DB::transaction(function () use ($issue,$status,$user,$message) {
            $old = $issue->status;
            $data = ['status'=>$status,'updated_by'=>$user->id];
            if ($status === 'resolved') $data['resolved_at'] = now();
            if ($status === 'closed') { $data['closed_at']=now(); $data['closed_by']=$user->id; }
            if ($status === 'open') { $data['resolved_at']=null; $data['closed_at']=null; $data['closed_by']=null; }
            $issue->update($data);
            $this->activity($issue,$user,'status_changed',$old,$status,$issue->assigned_to,$issue->assigned_to,$message ?: "Status changed from {$old} to {$status}");
            return $issue->fresh();
        });
    }

    public function assign(Issue $issue, ?int $staffId, $user): Issue
    {
        return DB::transaction(function () use ($issue,$staffId,$user) {
            $old = $issue->assigned_to;
            $issue->update(['assigned_to'=>$staffId,'updated_by'=>$user->id]);
            $this->activity($issue,$user,'assigned',$issue->status,$issue->status,$old,$staffId,$staffId ? 'Issue assigned' : 'Issue unassigned');
            return $issue->fresh(['assignee']);
        });
    }

    public function addComment(Issue $issue, array $data, $user): IssueComment
    {
        return DB::transaction(function () use ($issue,$data,$user) {
            $attachments = $this->storeFiles($data['attachment_files'] ?? null, []);
            $comment = $issue->comments()->create([
                'user_id'=>$user->id,'comment'=>$data['comment'],'is_internal'=>(bool)($data['is_internal'] ?? true),'attachments'=>$attachments,
            ]);
            $this->activity($issue,$user,'commented',$issue->status,$issue->status,$issue->assigned_to,$issue->assigned_to,'Comment added');
            return $comment->load('user');
        });
    }

    public function delete(Issue $issue): void
    {
        $issue->delete();
    }

    public function nextNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "ISS-{$year}-";
        $last = Issue::withTrashed()->where('issue_number','like',$prefix.'%')->lockForUpdate()->orderByDesc('id')->value('issue_number');
        $n = ($last && preg_match('/(\d+)$/',$last,$m)) ? ((int)$m[1]+1) : 1;
        return $prefix.str_pad((string)$n,6,'0',STR_PAD_LEFT);
    }

    private function activity(Issue $issue, $user, string $event, ?string $oldStatus, ?string $newStatus, $oldAssignee, $newAssignee, string $message): void
    {
        IssueActivity::create([
            'issue_id'=>$issue->id,'user_id'=>$user->id,'event_type'=>$event,'old_status'=>$oldStatus,'new_status'=>$newStatus,
            'old_assignee'=>$oldAssignee,'new_assignee'=>$newAssignee,'message'=>$message,
        ]);
    }

    private function storeFiles($files, array $existing): array
    {
        if (!$files) return $existing;
        $files = is_array($files) ? $files : [$files];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $path = $file->store('issues','public');
                $existing[] = ['path'=>$path,'name'=>$file->getClientOriginalName(),'uploaded_at'=>now()->toDateTimeString()];
            }
        }
        return $existing;
    }
}
