<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Http\Resources\PublisherResource;
use App\Models\Publisher;
use Illuminate\Http\Request;

/**
 * @group Publishers
 */
class PublisherController extends Controller
{
    /**
     * @unauthenticated
     *
     * @queryParam page integer Page number. Example: 1
     * @queryParam per_page integer Results per page (max 100). Example: 15
     */
    public function index()
    {
        $this->authorize('viewAny', Publisher::class);

        $publishers = Publisher::paginate(15);

        return PublisherResource::collection($publishers);
    }

    /**
     * @authenticated
     */
    public function store(Request $request)
    {
        $this->authorize('create', Publisher::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $publisher = Publisher::create($validated);

        return (new PublisherResource($publisher))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @unauthenticated
     */
    public function show(Publisher $publisher)
    {
        $this->authorize('view', $publisher);

        return new PublisherResource($publisher);
    }

    /**
     * @authenticated
     */
    public function update(Request $request, Publisher $publisher)
    {
        $this->authorize('update', $publisher);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'country' => ['sometimes', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $publisher->update($validated);

        return new PublisherResource($publisher);
    }

    /**
     * @authenticated
     */
    public function destroy(Publisher $publisher)
    {
        $this->authorize('delete', $publisher);

        if ($publisher->books()->exists()) {
            throw new BusinessException(
                'Cannot delete a publisher that has books.'
            );
        }

        $publisher->delete();

        return response()->json(null, 204);
    }
}
