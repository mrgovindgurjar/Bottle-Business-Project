<?php

namespace App\Http\Controllers\Admin;

use App\Events\DesignApproved;
use App\Http\Controllers\Controller;
use App\Http\Requests\DesignCommentRequest;
use App\Http\Requests\SaveDesignVersionRequest;
use App\Http\Requests\StoreDesignRequest;
use App\Models\Customer;
use App\Models\DesignRequest;
use App\Models\DesignVersion;
use App\Models\Product;
use App\Services\DesignStudioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DesignStudioController extends Controller
{
    public function __construct(private DesignStudioService $service) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', DesignRequest::class);
        $query = DesignRequest::with(['customer','product','versions'])->latest();
        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($x) use ($q) {
                $x->where('design_code', 'like', "%$q%")
                  ->orWhere('title', 'like', "%$q%")
                  ->orWhereHas('customer', fn ($c) => $c->where('business_name', 'like', "%$q%"));
            });
        }
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('type')) $query->where('design_type', $request->string('type'));
        $designs = $query->paginate(12)->withQueryString();
        $stats = [
            'total' => DesignRequest::count(),
            'draft' => DesignRequest::where('status','draft')->count(),
            'review' => DesignRequest::where('status','in_review')->count(),
            'approved' => DesignRequest::where('status','approved')->count(),
        ];
        return view('admin.design-studio.index', compact('designs','stats'));
    }

    public function create()
    {
        $this->authorize('create', DesignRequest::class);
        return view('admin.design-studio.create', [
            'customers' => Customer::where('status','active')->orderBy('business_name')->get(),
            'products' => Product::where('status','active')->orderBy('name')->get(),
            'defaults' => $this->service->defaultDesign(),
        ]);
    }

    public function store(StoreDesignRequest $request)
    {
        $this->authorize('create', DesignRequest::class);
        $design = $this->service->create(array_merge($request->validated(), ['design_data' => $this->service->defaultDesign()]), auth()->id());
        return redirect()->route('admin.designs.edit', $design)->with('success','Design project created.');
    }

    public function edit(DesignRequest $design)
    {
        $this->authorize('update', $design);
        $design->load(['customer','product','versions.comments.user']);
        $version = $design->versions->sortByDesc('version_no')->first();
        return view('admin.design-studio.edit', compact('design','version'));
    }

    public function show(DesignRequest $design)
    {
        $this->authorize('view', $design);
        $design->load(['customer','product','versions.comments.user','approver']);
        $version = $design->versions->sortByDesc('version_no')->first();
        return view('admin.design-studio.show', compact('design','version'));
    }

    public function save(SaveDesignVersionRequest $request, DesignRequest $design, DesignVersion $version)
    {
        $this->authorize('update', $design);
        abort_unless($version->design_request_id === $design->id, 404);
        $validated = $request->validated();
        $validated['design_data'] = json_decode($validated['design_data'], true);
        if (!is_array($validated['design_data'])) return back()->withErrors(['design_data' => 'Invalid design data.']);
        $this->service->updateVersion($design, $version, $validated, auth()->id());
        return back()->with('success','Design draft saved.');
    }

    public function newVersion(SaveDesignVersionRequest $request, DesignRequest $design, DesignVersion $version)
    {
        $this->authorize('update', $design);
        abort_unless($version->design_request_id === $design->id, 404);
        $validated = $request->validated();
        $designData = json_decode($validated['design_data'], true);
        if (!is_array($designData)) return back()->withErrors(['design_data' => 'Invalid design data.']);
        $new = $this->service->createVersion($design, $designData, auth()->id(), $validated['change_note'] ?? null);
        return redirect()->route('admin.designs.edit', $design)->with('success','New design version created: V'.$new->version_no);
    }

    public function logo(Request $request, DesignRequest $design, DesignVersion $version)
    {
        $this->authorize('update', $design);
        abort_unless($version->design_request_id === $design->id, 404);
        $request->validate(['logo' => ['required','file','mimes:png,jpg,jpeg,webp,svg','max:4096']]);
        $this->service->uploadLogo($version, $request->file('logo'));
        return back()->with('success','Logo uploaded.');
    }

    public function artwork(Request $request, DesignRequest $design, DesignVersion $version): JsonResponse
    {
        $this->authorize('update', $design);
        abort_unless($version->design_request_id === $design->id, 404);

        $request->validate([
            'front_artwork' => ['nullable','file','mimes:png,jpg,jpeg,webp','max:6144'],
            'back_artwork' => ['nullable','file','mimes:png,jpg,jpeg,webp','max:6144'],
        ]);

        $updated = $this->service->uploadArtwork(
            $version,
            $request->file('front_artwork'),
            $request->file('back_artwork')
        );

        return response()->json([
            'success' => true,
            'front_artwork_path' => $updated->front_artwork_path,
            'back_artwork_path' => $updated->back_artwork_path,
        ]);
    }

    public function submit(DesignRequest $design, DesignVersion $version)
    {
        $this->authorize('update', $design);
        abort_unless($version->design_request_id === $design->id, 404);
        $this->service->submit($design, $version);
        return back()->with('success','Design submitted for review.');
    }

    public function approve(DesignRequest $design, DesignVersion $version)
    {
        $this->authorize('approve', $design);
        abort_unless($version->design_request_id === $design->id, 404);
        $this->service->approve($design, $version, auth()->id());
        event(new DesignApproved($design, $version));
        return back()->with('success','Design V'.$version->version_no.' approved.');
    }

    public function changes(DesignCommentRequest $request, DesignRequest $design, DesignVersion $version)
    {
        $this->authorize('approve', $design);
        abort_unless($version->design_request_id === $design->id, 404);
        $this->service->requestChanges($design, $version, $request->validated()['comment']);
        return back()->with('success','Changes requested from this version.');
    }

    public function comment(DesignCommentRequest $request, DesignRequest $design, DesignVersion $version)
    {
        $this->authorize('comment', $design);
        abort_unless($version->design_request_id === $design->id, 404);
        $version->comments()->create(['user_id' => auth()->id(), 'type' => 'comment', 'comment' => $request->validated()['comment']]);
        return back()->with('success','Comment added.');
    }
}
