<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;
use App\Models\Actor;
use App\Http\Requests\StoreActorRequest;
use App\Http\Requests\UpdateActorRequest;
use App\Http\Resources\ActorCollection;
use App\Http\Resources\ActorResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start building the query
        $query = Actor::query();

        // Get the 'sort' parameter from the query string
        $sort = $request->query('sort');

        // If 'sort' parameter exists, process the sorting
        if ($sort) {
            // Split the 'sort' parameter by commas
            $sortParams = explode(',', $sort);

            foreach ($sortParams as $param) {
                // Split each sort parameter into column and direction
                list($column, $direction) = explode('$', $param);

                // Convert to camelCase if necessary, and apply sorting
                if (in_array($direction, ['ASC', 'DESC']) && in_array($column, ['firstName', 'lastName', 'birthday', 'height'])) {
                    $query->orderBy($this->camelToSnake($column), $direction);
                }
            }
        }

        // Filtrowanie wyników na podstawie przekazanych parametrów
        if ($request->has('firstName')) {
            $query->where('first_name', 'like', '%' . $request->query('firstName') . '%');
        }

        if ($request->has('lastName')) {
            $query->where('last_name', 'like', '%' . $request->query('lastName') . '%');
        }

        if ($request->has('birthday')) {
            $query->where('birthday', '=', $request->query('birthday'));
        }

        if ($request->has('height')) {
            $query->where('height', '=', $request->query('height'));
        }


        $page = $request->query('page', 1); // Domyślnie 1
        $pageSize = $request->query('pageSize', 10); // Domyślnie 10

        // Ustawienie paginacji
        $actors = $query->paginate($pageSize, ['*'], 'page', $page);

        // Execute the query and return the collection of actors
        return new ActorCollection($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreActorRequest $request)
    {
        return new ActorResource(Actor::create($request->validated()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Actor $actor)
    {
        return new ActorResource($actor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateActorRequest $request, Actor $actor)
    {
        $actor->update($request->validated());
        $actor = $actor->refresh();
        return new ActorResource($actor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Actor $actor)
    {
        $actor->delete();
        return response()->json(null, 204);
    }

    private function camelToSnake($camelCase)
    {
        return Str::snake($camelCase);
    }
}
