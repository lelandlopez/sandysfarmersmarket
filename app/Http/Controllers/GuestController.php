<?php

namespace App\Http\Controllers;

use Auth;
use App\Farmers_Market_Hour;
use App\Farmers_Market_Review;
use App\Farmers_Market;
use App\Review;
use App\Farmers_Market_Vendor;
use App\Vendor;
use App\Vendor_Item;
use App\Patron;
use App\Event;
use App\User;
use App\DB;
use App\Photo;
use App\Farmers_Market_Vendor_Map;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Http\Response;

class GuestController extends Controller
{
    public function find() {
    	date_default_timezone_set('US/Hawaii');
    	$day = Date("D");
    	$hour = Date("g");
    	$min = Date("i");
    	if($min > 0 && $min < 15) {
    		$min = 15;
    	}
    	elseif($min > 15 && $min < 30) {
    		$min = 30;
    	}
    	elseif($min > 30 && $min < 45) {
    		$min = 45;
    	}
    	elseif($min > 45 && $min < 60) {
    		$min = 0;
    	}
    	$period = Date("A");
        return view('find')
        	->with('day', $day)
        	->with('hour', $hour)
        	->with('min', $min)
        	->with('period', $period);
    }
    public function farmers_market($id) {
        $farmers_market = Farmers_Market::find($id);
        $farmers_market_reviews = Review::where('reviewed_id', $farmers_market->user_id)->get();
        $average_rating = 0;
        for($i = 0; $i < count($farmers_market_reviews); $i++) {
            $average_rating += $farmers_market_reviews[$i]->rating;
        }
        if($average_rating != 0) {
            $average_rating = $average_rating/count($farmers_market_reviews); 
        }
        $vendor_ids = Farmers_Market_Vendor::where('farmers_market_id', $id)->get();
        $items_query = \DB::table('vendor_items');
        for($i = 0; $i < count($vendor_ids); $i++) {
            $items_query = $items_query->where('vendor_id', $vendor_ids[$i]->vendor_id);
        }
        $events = Event::where('user_id', Farmers_Market::find($id)->user_id)->get();
        $lowest_review = Review::where('reviewed_id', $farmers_market->user_id)
        ->orderBy('rating', 'asc')
        ->first();
        $highest_review = Review::where('reviewed_id', $farmers_market->user_id)
        ->orderBy('rating', 'desc')
        ->first();
        $monday_hours = Farmers_Market_Hour::where('day_of_week', '1')
            ->where('farmers_market_id', $farmers_market->id)->get();
        $tuesday_hours = Farmers_Market_Hour::where('day_of_week', '2')
            ->where('farmers_market_id', $farmers_market->id)->get();
        $wednesday_hours = Farmers_Market_Hour::where('day_of_week', '3')
            ->where('farmers_market_id', $farmers_market->id)->get();
        $thursday_hours = Farmers_Market_Hour::where('day_of_week', '4')
            ->where('farmers_market_id', $farmers_market->id)->get();
        $friday_hours = Farmers_Market_Hour::where('day_of_week', '5')
            ->where('farmers_market_id', $farmers_market->id)->get();
        $saturday_hours = Farmers_Market_Hour::where('day_of_week', '6')
            ->where('farmers_market_id', $farmers_market->id)->get();
        $sunday_hours = Farmers_Market_Hour::where('day_of_week', '7')
            ->where('farmers_market_id', $farmers_market->id)->get();
        return view('farmers_market')
            ->with('farmers_market', $farmers_market)
            ->with('farmers_market_reviews', $farmers_market_reviews)
            ->with('events', $events)
            ->with('lowest_review', $lowest_review)
            ->with('farmers_market_items', $items_query->get())
            ->with('highest_review', $highest_review)
            ->with('average_rating', $average_rating)
            ->with('monday_hours', $monday_hours)
            ->with('tuesday_hours', $tuesday_hours)
            ->with('wednesday_hours', $wednesday_hours)
            ->with('thursday_hours', $thursday_hours)
            ->with('friday_hours', $friday_hours)
            ->with('saturday_hours', $saturday_hours)
            ->with('sunday_hours', $sunday_hours)
            ->with('vendors_id', $vendor_ids);
    }
    public function farmers_market_review() {
        return view('farmers_market_review');
    }
    public function post_farmers_market_review(Request $request, $id) {
        $comment = new Review;
        $comment->reviewed_id = $id;
        $comment->reviewer_id = Auth::id();
        $comment->review = $request->review;
        $comment->save();
        $url = '/farmers_market/' . $id;
        return redirect($url);
    }
    public function vendor($id) {
        $vendor = Vendor::find($id);
        $vendor_items = Vendor_Item::where('vendor_id', $id)->get();
        $vendor_reviews = Review::where('reviewed_id', $vendor->user_id)->get();
        $lowest_review = Review::where('reviewed_id', $vendor->user_id)
        ->orderBy('rating', 'asc')
        ->first();
        $highest_review = Review::where('reviewed_id', $vendor->user_id)
        ->orderBy('rating', 'desc')
        ->first();
        $vendor_items = Vendor_Item::where('vendor_id', $vendor->id)->get();
        $average_rating = 0;
        for($i = 0; $i < count($vendor_reviews); $i++) {
            $average_rating += $vendor_reviews[$i]->rating;
        }
        if($average_rating != 0) {
            $average_rating = $average_rating/count($vendor_reviews);
        }
        return view('vendor')
            ->with('vendor', $vendor)
            ->with('vendor_reviews', $vendor_reviews)
            ->with('lowest_review', $lowest_review)
            ->with('highest_review', $highest_review)
            ->with('average_rating', $average_rating)
            ->with('vendor_items', $vendor_items)
            ->with('vendor', $vendor);
    }
    public function patron($id) {
        $patron = Patron::find($id);
        $patron_reviews = Review::where('reviewer_id', $patron->user_id)->get();
        return view('patron')
            ->with('patron_reviews', $patron_reviews)
            ->with('patron', $patron);
    }
    public function find_farmers_markets() {
        date_default_timezone_set('US/Hawaii');
        $day = Date("D");
        $hour = Date("g");
        $min = Date("i");
        if($min > 0 && $min < 15) {
            $min = 15;
        }
        elseif($min > 15 && $min < 30) {
            $min = 30;
        }
        elseif($min > 30 && $min < 45) {
            $min = 45;
        }
        elseif($min > 45 && $min < 60) {
            $min = 0;
        }
        $period = Date("A");
    
        return view('find.farmers_markets')
            ->with('day', $day)
            ->with('hour', $hour)
            ->with('min', $min)
            ->with('period', $period);

    }

    public function get($filename){
        $entry = Photo::where('filename', '=', $filename)->firstOrFail();
        $file = Storage::disk('local')->get('/photos/' . $entry->filename);
        return (new Response($file, 200))
              ->header('Content-Type', $entry->mime);
    }
    public function farmers_market_vendor_map($id) {
        $map = Farmers_Market_Vendor_Map::where('farmers_market_id', $id)->first();
        $photo = null;
        if($map != null) {
            $photo = Photo::find($map->photo_id);
        }
        $vendor_ids = Farmers_Market_Vendor::where('farmers_market_id', $id)->get();
        return view('farmers_market_vendor_map')
            ->with('photo', $photo)
            ->with('vendor_ids', $vendor_ids)
            ->with('id', $id);
    }
}
