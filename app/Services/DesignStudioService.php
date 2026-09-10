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

            $this->createVersion($request, $data['design_data'] ?? $this->defaultDesign(), $userId, $data['change_note'] ?? null);
            return $request;
        });
    }

    public function updateVersion(DesignRequest $request, DesignVersion $version, array $data, int $userId): DesignVersion
    {
        $version->update([
            'design_data' => $data['design_data'],
            'name' => $data['version_name'] ?? $version->name,
            'change_note' => $data['change_note'] ?? null,
            'status' => 'draft',
        ]);
        $request->update(['status' => 'draft']);
        return $version->fresh();
    }

    public function createVersion(DesignRequest $request, array $designData, int $userId, ?string $note = null): DesignVersion
    {
        $next = ((int) $request->versions()->max('version_no')) + 1;
        return $request->versions()->create([
            'version_no' => $next,
            'name' => 'Version '.$next,
            'design_data' => $designData,
            'status' => 'draft',
            'change_note' => $note,
            'created_by' => $userId,
        ]);
    }

    public function uploadLogo(DesignVersion $version, UploadedFile $file): DesignVersion
    {
        if ($version->logo_path) Storage::disk('public')->delete($version->logo_path);
        $version->update(['logo_path' => $file->store('design-studio/logos', 'public')]);
        return $version->fresh();
    }

    public function submit(DesignRequest $request, DesignVersion $version): void
    {
        if ($version->status === 'approved') {
            throw ValidationException::withMessages(['design' => 'An approved version cannot be submitted again. Create a new version.']);
        }
        $version->update(['status' => 'submitted']);
        $request->update(['status' => 'in_review', 'submitted_at' => now()]);
    }

    public function approve(DesignRequest $request, DesignVersion $version, int $userId): void
    {
        DB::transaction(function () use ($request, $version, $userId) {
            $request->versions()->whereKeyNot($version->id)->where('status', 'approved')->update(['status' => 'archived']);
            $version->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
            $request->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
        });
    }

    public function requestChanges(DesignRequest $request, DesignVersion $version, string $note): void
    {
        $version->update(['status' => 'changes_requested']);
        $request->update(['status' => 'changes_requested']);
        $version->comments()->create(['user_id' => auth()->id(), 'type' => 'change_request', 'comment' => $note]);
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

    private function code(): string
    {
        do { $code = 'DS-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)); }
        while (DesignRequest::where('design_code', $code)->exists());
        return $code;
    }
}
