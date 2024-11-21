<?php

namespace App\Http\Controllers;
use App\Models\Movie;
use App\Models\Actor;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Http\Resources\MovieCollection;
use App\Http\Resources\ActorCollection;
use App\Http\Resources\MovieResource;
use Illuminate\Http\Request;

class MovieController extends Controller
{

    public function getMovieCast(Movie $movie){
        return new ActorCollection($movie->actors);
    }

    public function addActorToMovie(Request $request, Movie $movie)
    {
        $actorId = $request->input('actor_id');
        $actor = Actor::find($actorId);

        if (!$actor) {
            return response()->json(['error' => 'Actor not found'], 404);
        }

        if (!$movie->actors()->where('actors.id', $actorId)->exists()) {
            $movie->actors()->attach($actorId);
            return new ActorCollection($movie->actors);
        }

        return response()->json(['message' => 'Actor is already in the cast'], 200);
    }

    public function removeActorFromMovie(Movie $movie, $actorId)
    {
        $actor = $movie->actors()->find($actorId);

        if (!$actor) {
            return response()->json(['error' => 'Actor not found in this movie'], 404);
        }

        $movie->actors()->detach($actorId);
        return new ActorCollection($movie->actors);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $includeActors = $request->query('includeActors', false);

        if ($includeActors === 'true') {
            // Załaduj filmy wraz z aktorami
            $movies = Movie::with('actors')->get();
        } else {
            // Załaduj tylko filmy
            $movies = Movie::all();
        }

        return new MovieCollection($movies);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMovieRequest $request)
    {
        return new MovieResource(Movie::create($request->validated()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Movie $movie)
    {
        return new MovieResource($movie);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMovieRequest $request, Movie $movie)
    {
        $movie->update($request->validated());
        $movie = $movie->refresh();
        return new MovieResource($movie);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();
        return response()->json(null, 204);
    }

}
