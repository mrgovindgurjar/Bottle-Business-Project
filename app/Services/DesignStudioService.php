<?php

namespace App\Services;

use App\Models\DesignRequest;
use App\Models\DesignVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DesignStudioService
{
    public function create(array $data, int $userId): DesignRequest
    {
        return DB::transaction(function () use ($data, $userId) {
            $request = DesignRequest::create([
                'design_code' => $this->code(),
                'customer_id' => $data['customer_id'],
                'product_id' => $data['product_id'] ?? null,
                'created_by' => $userId,
                'title' => $data['title'],
                'design_type' => $data['design_type'] ?? 'restaurant',
                'status' => 'draft',
                'brief' => $data['brief'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            $this->createVersion(
                $request,
                $data['design_data'] ?? $this->defaultDesign(),
                $userId,
                $data['change_note'] ?? null
            );

            $this->audit('created', $request, null, [
                'design_code' => $request->design_code,
                'customer_id' => $request->customer_id,
            ], $userId, 'Design request created');

            return $request->fresh('versions');
        });
    }

    public function updateVersion(
        DesignRequest $request,
        DesignVersion $version,
        array $data,
        int $userId
    ): DesignVersion {
        return DB::transaction(function () use ($request, $version, $data, $userId) {
            $lockedRequest = DesignRequest::lockForUpdate()->findOrFail($request->id);
            $lockedVersion = DesignVersion::where('design_request_id', $lockedRequest->id)
                ->lockForUpdate()
                ->findOrFail($version->id);

            if ($lockedVersion->status === 'approved') {
                throw ValidationException::withMessages([
                    'design' => 'Approved versions are locked. Create a new version for further changes.',
                ]);
            }

            if (in_array($lockedRequest->status, ['approved', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'design' => 'This design project is locked and cannot be edited.',
                ]);
            }

            $old = $lockedVersion->only(['name', 'design_data', 'change_note', 'status']);

            $lockedVersion->update([
                'design_data' => $data['design_data'],
                'name' => $data['version_name'] ?? $lockedVersion->name,
                'change_note' => $data['change_note'] ?? $lockedVersion->change_note,
                'status' => 'draft',
            ]);

            $lockedRequest->update(['status' => 'draft']);

            $updated = $lockedVersion->fresh();
            $this->audit('updated', $updated, $old, $updated->only(['name', 'design_data', 'change_note', 'status']), $userId, 'Design version updated');

            return $updated;
        });
    }

    public function createVersion(DesignRequest $request, array $designData, int $userId, ?string $note = null): DesignVersion
    {
        return DB::transaction(function () use ($request, $designData, $userId, $note) {
            $lockedRequest = DesignRequest::lockForUpdate()->findOrFail($request->id);

            if ($lockedRequest->status === 'cancelled') {
                throw ValidationException::withMessages(['design' => 'A cancelled design cannot receive a new version.']);
            }

            $next = ((int) $lockedRequest->versions()->lockForUpdate()->max('version_no')) + 1;

            $version = $lockedRequest->versions()->create([
                'version_no' => $next,
                'name' => 'Version '.$next,
                'design_data' => $designData,
                'status' => 'draft',
                'change_note' => $note,
                'created_by' => $userId,
            ]);

            $lockedRequest->update(['status' => 'draft']);
            $this->audit('version_created', $version, null, ['version_no' => $next], $userId, 'New design version created');

            return $version->fresh();
        });
    }

    public function uploadLogo(DesignVersion $version, UploadedFile $file, int $userId): DesignVersion
    {
        $version = $this->lockEditableVersion($version);
        $oldPath = $version->logo_path;
        $newPath = $file->store('design-studio/logos', 'public');

        try {
            $version->update(['logo_path' => $newPath, 'status' => 'draft']);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($newPath);
            throw $e;
        }

        $this->audit('logo_uploaded', $version, ['logo_path' => $oldPath], ['logo_path' => $newPath], $userId, 'Design logo uploaded');
        return $version->fresh();
    }

    public function uploadArtwork(DesignVersion $version, ?UploadedFile $front = null, ?UploadedFile $back = null, ?int $userId = null): DesignVersion
    {
        $version = $this->lockEditableVersion($version);
        $old = $version->only(['front_artwork_path', 'back_artwork_path']);
        $new = [];
        $stored = [];

        try {
            if ($front) {
                $stored['front'] = $front->store('design-studio/artwork/front', 'public');
                $new['front_artwork_path'] = $stored['front'];
            }
            if ($back) {
                $stored['back'] = $back->store('design-studio/artwork/back', 'public');
                $new['back_artwork_path'] = $stored['back'];
            }

            if ($new) {
                $version->update(array_merge($new, ['status' => 'draft']));
            }

            if (!empty($new['front_artwork_path']) && $old['front_artwork_path']) {
                Storage::disk('public')->delete($old['front_artwork_path']);
            }
            if (!empty($new['back_artwork_path']) && $old['back_artwork_path']) {
                Storage::disk('public')->delete($old['back_artwork_path']);
            }
        } catch (\Throwable $e) {
            foreach ($stored as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        if ($userId) {
            $this->audit('artwork_uploaded', $version, $old, $version->only(['front_artwork_path', 'back_artwork_path']), $userId, 'Design artwork uploaded');
        }

        return $version->fresh();
    }

    public function submit(DesignRequest $request, DesignVersion $version, int $userId): void
    {
        DB::transaction(function () use ($request, $version, $userId) {
            $lockedRequest = DesignRequest::lockForUpdate()->findOrFail($request->id);
            $lockedVersion = DesignVersion::where('design_request_id', $lockedRequest->id)->lockForUpdate()->findOrFail($version->id);

            if ($lockedVersion->status === 'approved') {
                throw ValidationException::withMessages(['design' => 'An approved version cannot be submitted again. Create a new version.']);
            }
            if (!in_array($lockedVersion->status, ['draft', 'changes_requested'], true)) {
                throw ValidationException::withMessages(['design' => 'Only a draft or changed version can be submitted.']);
            }

            $lockedVersion->update(['status' => 'submitted']);
            $lockedRequest->update(['status' => 'in_review', 'submitted_at' => now()]);
            $this->audit('submitted', $lockedVersion, null, ['status' => 'submitted'], $userId, 'Design version submitted for approval');
        });
    }

    public function approve(DesignRequest $request, DesignVersion $version, int $userId): void
    {
        DB::transaction(function () use ($request, $version, $userId) {
            $lockedRequest = DesignRequest::lockForUpdate()->findOrFail($request->id);
            $lockedVersion = DesignVersion::where('design_request_id', $lockedRequest->id)->lockForUpdate()->findOrFail($version->id);

            if (!in_array($lockedVersion->status, ['submitted', 'changes_requested'], true)) {
                throw ValidationException::withMessages(['design' => 'Only a submitted version can be approved.']);
            }

            $lockedRequest->versions()
                ->whereKeyNot($lockedVersion->id)
                ->where('status', 'approved')
                ->update(['status' => 'archived']);

            $lockedVersion->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);
            $lockedRequest->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            $this->audit('approved', $lockedVersion, null, ['status' => 'approved'], $userId, 'Design version approved');
        });
    }

    public function requestChanges(DesignRequest $request, DesignVersion $version, string $note, int $userId): void
    {
        DB::transaction(function () use ($request, $version, $note, $userId) {
            $lockedRequest = DesignRequest::lockForUpdate()->findOrFail($request->id);
            $lockedVersion = DesignVersion::where('design_request_id', $lockedRequest->id)->lockForUpdate()->findOrFail($version->id);

            if ($lockedVersion->status === 'approved') {
                throw ValidationException::withMessages(['design' => 'Approved versions cannot be sent back for changes. Create a new version.']);
            }

            $lockedVersion->update(['status' => 'changes_requested']);
            $lockedRequest->update(['status' => 'changes_requested']);
            $lockedVersion->comments()->create([
                'user_id' => $userId,
                'type' => 'change_request',
                'comment' => $note,
            ]);

            $this->audit('changes_requested', $lockedVersion, null, ['status' => 'changes_requested', 'comment' => $note], $userId, 'Design changes requested');
        });
    }

    public function defaultDesign(): array
    {
        return [
            'global' => ['label_style' => 'premium', 'font' => 'Inter', 'front_back' => 'both'],
            'front' => $this->sideDefaults('front'),
            'back' => $this->sideDefaults('back'),
        ];
    }

    private function sideDefaults(string $side): array
    {
        return [
            'background' => '#ffffff',
            'accent' => '#111827',
            'title' => $side === 'front' ? 'PURE TASTE' : 'OUR MENU',
            'subtitle' => $side === 'front' ? 'Premium drinking water' : 'Scan to explore',
            'body' => $side === 'front' ? 'CUSTOM BRANDED WATER' : 'Your brand. Your story. Your bottle.',
            'footer' => $side === 'front' ? 'PURE • SAFE • PREMIUM' : 'THANK YOU • VISIT AGAIN',
            'logo_x' => 50, 'logo_y' => 16, 'logo_scale' => 1,
            'qr_x' => 50, 'qr_y' => 74, 'qr_size' => 22,
        ];
    }

    private function lockEditableVersion(DesignVersion $version): DesignVersion
    {
        $locked = DesignVersion::lockForUpdate()->findOrFail($version->id);
        if ($locked->status === 'approved') {
            throw ValidationException::withMessages(['design' => 'Approved versions are locked. Create a new version first.']);
        }
        return $locked;
    }

    private function audit(string $event, object $auditable, ?array $old, ?array $new, int $userId, string $description): void
    {
        if (!class_exists(\App\Models\AuditLog::class)) return;
        \App\Models\AuditLog::record($event, $auditable, $old, $new, $description, $userId);
    }

    private function code(): string
    {
        do {
            $code = 'DS-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while (DesignRequest::where('design_code', $code)->exists());
        return $code;
    }
}
