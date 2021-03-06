<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
	protected $primaryKey = 'id_user';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'login',
        'email',
        'password',
        'pp',
        'country',
        'address',
        'number',
        'district',
        'state',
        'city',
        'tel',
        'tutorial',
        'complements',
        'name',
        'image_url',
        'complet',
        'complet_phone',
        'complet_friends_email',
        'complet_friend'
	];
	
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token','api_token',
	];

    public function cards()
    {
	    $cards = UserCards::where('id_user','=',$this->id_user)->orderBy('created_at','desc')->get();
	    $result = [];
	    foreach ($cards as $key => $user_card) {
		    $result[$key] = [
			    'id_user_card' => $user_card->id_user_card,
			    'created_at' => $user_card->created_at->toDateTimeString(),
			    'foil' => $user_card->foil,
			    'reverse_foil' => $user_card->reverse_foil,
			    'full_art' => $user_card->full_art,
		    ];
		    $result[$key]['card'] = (array)$user_card->card->fullset();
	    }
        return ($result)?$result:[];
    }

    public function wants()
    {
        $want = Want::where('id_user','=',$this->id_user)->where('id_status_want','=',1)->orderBy('created_at','desc')->get();
        $result = [];
        foreach ($want as $key => $user_want) {
            $result[$key] = $user_want->toArray();
            $result[$key]['card'] = (array)$user_want   ->card->fullset();
        }
        return ($result)?$result:[];
    }
    
    public function trades()
    {
        $want = Want::where('id_user','=',$this->id_user)->where('id_status_want','=',3)->orderBy('created_at','desc')->get();
        $result = [];
        foreach ($want as $key => $user_want) {
            $result[$key] = $user_want->toArray();
            $result[$key]['card'] = (array)$user_want->card->fullset();
        }
        return ($result)?$result:[];
    }
    
    public function fullSet($options = null)
    {
    	$user = new \stdClass();
	    $user->id_user = $this->id_user;
	    $user->login = $this->login;
	    $user->name = $this->name;
	    $user->address = $this->address;
	    $user->number = $this->number;
	    $user->city = $this->city;
	    $user->state = $this->state;
	    $user->district = $this->district;
	    $user->country = $this->country;
	    $user->email = $this->email;
	    $user->pp = $this->pp;
	    $user->created_at = $this->created_at->toDateTimeString();
	    $user->image_url = $this->image_url;
	    $wants = $this->wants();
	    $pp_wants = 0;
	    foreach ($wants as $want) {
		    $pp_wants += $want['pp'];
	    }
	    if(!isset($options["wants"]) || isset($options["wants"]) && $options["wants"] === true)
	    	$user->wants = $wants;
	    
	    $user->pp_wants = $pp_wants;
	    
	    $trades = $this->trades();
	    $pp_trade = 0;
	    foreach ($trades as $trade) {
		    $pp_trade += $trade['pp'];
	    }
	    $user->trades = $trades;
	    $user->pp_trades = $pp_trade;
	    
	    if(!isset($options["cards"]) || isset($options["cards"]) && $options["cards"] === true)
	    	$user->cards = $this->cards();
	    
	    return $user;
    }

}
