<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Events\TripCreated;
use App\Events\TripEnded;
use App\Events\TripLoactionUpdated;
use App\Events\TripStrated;
use Illuminate\Http\Request;
use App\Models\Trip;

class TripController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'destination_name' => 'required',
        ]);

        $trip = $request->user()->trips()->create($request->only([
            'origin',
            'destination',
            'destination_name'
        ]));

        TripCreated::dispatch($trip, $request->user());

        return $trip;
    }

    public function show(Request $request, Trip $trip) {

        if($trip->user->id === $request->user()->id){
            return $trip;
        }

        if($trip->driver && $request->user()->driver){
            if($trip->driver->id === $request->user()->driver->id){
                return $trip;
            }
        }

        return response()->json(['message' => 'Can not find the trip.'], 404);
    }

    public function accept(Request $request, Trip $trip){
        $request->validate([
            'driver_location' => 'required'
        ]);

        $trip->update([
            'driver_id' => $request->user()->driver->id,
            'driver_location' => $request->driver_location,
        ]);

        $trip->load('driver.user');
        // $trip->driver->load('user');
        TripAccepted::dispatch($trip, $trip->user);
        return $trip;
    }

    public function start(Request $request, Trip $trip){

        $trip->update([
            'is_started' => true,
        ]);

        $trip->load('driver.user');

        TripStrated::dispatch($trip, $trip->user);
        return $trip;

    }

    public function end(Request $request, Trip $trip){
        $trip->update([
            'is_complete' => true,
        ]);

        $trip->load('driver.user');

        TripEnded::dispatch($trip, $trip->user);
        return $trip;
    }

    public function location(Request $request, Trip $trip){
        $request->validate([
            'driver_location' => 'required'
        ]);

        $trip->update([
            'driver_location' => $request->driver_location,
        ]);

        $trip->load('driver.user');
        TripLoactionUpdated::dispatch($trip, $trip->user);
        return $trip;
    }
}
