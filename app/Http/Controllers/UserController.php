<?php

namespace App\Http\Controllers;


use App\Exceptions\CustomExecption;
use App\Http\Requests\AddCard;
use App\Http\Requests\EditWant;
use App\Http\Requests\Login;
use App\Http\Requests\Register;
use App\Jobs\AddMesssage;
use App\Models\Cards;
use App\Models\History;
use App\Models\Message;
use App\Models\User;
use App\Models\UserCards;
use App\Models\Want;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery\CountValidator\Exception;

class UserController extends Controller
{
    public function login(Login $login)
    {

        if (Auth::check()) {
            $resoponse = [
                'status' => 'success',
                'user' => [
                    'id_user' => Auth::user()->id_user,
                    'login' => Auth::user()->login,
                    'pp' => Auth::user()->pp,
                    'email' => Auth::user()->email,
                ],
                'cache' => true
            ];
            if (Auth::user()->tutorial == false)
                $resoponse['tutorial'] = true;

            return response()->json($resoponse);
        }
        $creadentials = $login->only('login', 'password');
        $creadentials['login'] = strtolower($creadentials['login']);

        try {

            if (Auth::attempt($creadentials, true)) {

                $msg = 'Login ip ' . $login->ip();
                $this->dispatch(new AddMesssage(Auth::user(), $msg, 5));

                $resoponse = [
                    'status' => 'success',
                    'cache' => true,
                    'user' => [
                        'id_user' => Auth::user()->id_user,
                        'login' => Auth::user()->login,
                        'pp' => Auth::user()->pp,
                        'email' => Auth::user()->email,
                    ]
                ];

                if (Auth::user()->tutorial == false)
                    $resoponse['tutorial'] = true;

                return response()->json($resoponse);
            }

            return response()->json([
                'status' => 'warning',
                'warning' => 'Login or Password Invalid'
            ]);


        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }


    public function tutorial($type)
    {
        if ($type == 1)
            Auth::user()->tutorial = true;
        else
            Auth::user()->tutorial = false;

        Auth::user()->save();
    }

    public function register(Register $register)
    {
        try {
            DB::beginTransaction();
            $newUser = $register->all();
            $newUser['login'] = strtolower($newUser['login']);
            $newUser['email'] = strtolower($newUser['email']);
            $newUser['password'] = Hash::make($newUser['password']);

            $user = User::create($newUser);
            /*
            $email = new EmailController($newUser['email']);
            $email->view('welcome',$user->fullSet())->send();
            */

            Auth::login($user);

            $msg = 'Wellcome to PokeTrade.com';
            $this->dispatch(new AddMesssage(Auth::user(), $msg, 2, true));

            $msg = 'Register';
            $this->dispatch(new AddMesssage(Auth::user(), $msg, 3));

            DB::commit();
            return response()->json([
                'status' => 'success',
                'user' => [
                    'id_user' => $user->id_user,
                    'login' => $user->login,
                    'email' => $user->email,
                    'pp' => $user->pp
                ],
                'tutorial' => true
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return $this->_returnError('Register Fail', $e);
        }
    }

    public function logout()
    {
        if (\Auth::check()) {
            $msg = 'Logout';
            $this->dispatch(new AddMesssage(Auth::user(), $msg, 5));
            \Auth::logout();
        }
        return redirect('/');
    }

    /**
     * @return array
     */
    public function myCards()
    {
        try {
            $cards = \Auth::user()->cards();
            $result = [];

            $result['total_cards'] = count($cards);
            $result['card'] = $cards;

            return $this->_return('Get all cards user', 'success', $result);
        } catch (\Exception $e) {
            return $this->_returnError('Cards User Fail', $e);
        }
    }

    public function myMessages(Request $request)
    {
        try {

            $limit = 100;
            if($request->limit && $request->limit != "false")
                $limit = $request->limit;

            $field = ['text', 'created_at', 'id_status_message', 'id_user_from'];
            $query = Message::select($field)
                ->where('id_user', '=', Auth::user()->id_user)
                ->orderBy('created_at', 'desc');
            $query->take($limit);

            if ($request->id_status_message != null) {
                $query->whereIn('id_status_message', explode(',', $request->id_status_message));
            }


            if ($request->last) {
                $messages = [];
                $temp_message = $query->get();
                if ($request->id_status_message)
                    $only = explode(',', $request->id_status_message);
                else
                    $only = [1, 2, 3, 4, 5, 6];

                foreach ($temp_message as $msg) {
                    if (in_array($msg->id_status_message, $only)) {
                        $messages[] = $msg;
                        unset($only[array_search($msg->id_status_message, $only)]);
                    }
                    if (count($only) === 0)
                        break;
                }
            } else {
                $messages = $query->get();
            }
            foreach ($messages as $message) {
                $message->from;
            }
            return $this->_return('Message user', 'success', $messages);
        } catch (\Exception $e) {
            return $this->_returnError('Message user Fail', $e);
        }
    }

    public function profile($id_user)
    {
        try {

            if ($user = User::findOrFail($id_user)->fullSet(["wants"=>false,"cards"=>false])) {
                return ['status' => 'success', 'user' => $user];
            }
            return ['status' => 'warning', 'warning' => 'User not found'];
        } catch (\Exception $e) {
            return $this->_returnError('Fail get user', $e);
        }
    }

    public function addCard(AddCard $addCard)
    {
        try {
            DB::beginTransaction();
            $amount = $addCard->amount;
            if ($amount > 4)
                $amount = 4;

            $new_card = $addCard->all();

            if ($new_card['full_art'] === true) {
                $new_card['foil'] = false;
                $new_card['reverse_foil'] = false;
            }

            if ($new_card['full_art'] === false && $new_card['reverse_foil'] === true)
                $new_card['foil'] = true;


            $card = Cards::find($addCard->id_card);
            $msg = 'Add in my card list ' . $addCard->amount . ' new card \'' . $card->name . ' (#' . $card->number . '/' . $card->set->total_cards . ')\'';
            $this->dispatch(new AddMesssage(Auth::user(), $msg, 6));

            for ($i = 0; $i < $amount; $i++) {
                UserCards::create($new_card);
            }
            DB::commit();
            return $this->_return('Add card', 'success');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->_returnError('Add Card Fail', $e);
        }
    }

    public function addWant(AddCard $addCard)
    {
        try {
            $amount = $addCard->amount;
            if ($amount > 4)
                $amount = 4;

            if ($addCard->pp > 100000)
                throw new Exception('Invalid price, value over 100000');

            $card = Cards::find($addCard->id_card);
            $msg = 'Add in my want card list ' . $addCard->amount . ' new card \'' . $card->name . ' (#' . $card->number . '/' . $card->set->total_cards . ')' . '\' with ' . $addCard->pp . ' PokePoint';
            $this->dispatch(new AddMesssage(Auth::user(), $msg, 6));

            for ($i = 0; $i < $amount; $i++) {
                Want::create($addCard->all());
            }
            return $this->_return('Add want card', 'success');
        } catch (\Exception $e) {
            return $this->_returnError('Add Want Card Fail', $e);
        }
    }

    public function myWantList()
    {
        try {

            $wants = \Auth::user()->wants();

            return $this->_return('Get Want List', 'success', ($wants) ? $wants : []);
        } catch (\Exception $e) {
            return $this->_returnError('Want List Fail', $e);
        }
    }

    public function removeWant($id_want)
    {
        try {

            $want = Want::find($id_want);
            $card = Cards::find($want->id_card);
            $msg = 'Remove in my want card list \'' . $card->name . ' (#' . $card->number . '/' . $card->set->total_cards . ')' . '\' with ' . $want->pp . ' PokePoint';
            $this->dispatch(new AddMesssage(Auth::user(), $msg, 6));

            $want->delete();

            return $this->_return('Remove Want List', 'success');
        } catch (\Exception $e) {
            return $this->_returnError('Remove Want List Fail', $e);
        }
    }

    public function editWant($id_want, EditWant $editWant)
    {
        try {

            if ($editWant->pp > 100000)
                throw new Exception('Invalid price, value over 100000');

            $want = Want::find($id_want);
            $old_pp = $want->pp;
            $want->update($editWant->all());


            $msg = 'You edit ' . $want->card->name . '(#' . $want->card->number . '/' . $want->card->set->total_cards . ') from ' . $old_pp . ' to ' . $want->pp . ' PokePoint';
            $this->dispatch(new AddMesssage(Auth::user(), $msg, 6));

            return $this->_return('Edit Want List', 'success');
        } catch (\Exception $e) {
            return $this->_returnError('Edit Want List Fail', $e);
        }
    }

    public function sendWant($id_want)
    {
        try {

            \DB::beginTransaction();

            $user = \Auth::user();
            $want = Want::find($id_want);


            if ($user->id_user == $want->id_user)
                throw new CustomExecption("User send self card");


            History::create([
                'id_user' => $want->user->id_user,
                'id_want' => $want->id_want,
                'text' => 'Card added at '.$want->created_at->toDateTimeString().' by '.$this->_link($want->user->login,'/user/'.$want->user->id_user)
            ]);

            $want->id_user_from = \Auth::user()->id_user;
            $want->id_status_want = 2;
            $want->save();

            $cards_user = $user->cards();

            $have = false;
            foreach ($cards_user as $key => $value) {
                if ($value['card']['id_card'] == $want->id_card) {
                    $this->remove($value['id_user_card']);
                    $have = true;
                    break;
                }
            }

            if ($have == false)
                throw new CustomExecption('User don\'t have this card');

            if ($want->pp > $want->user->pp)
                throw new CustomExecption('User don\'t have pp for this trade');

            $want->user->pp_reserve = ($want->user->pp_reserve) ? $want->user->pp_reserve : 0 + $want->pp;
            $want->user->pp -= $want->pp;
            $want->user->save();


            History::create([
                'id_user' => $want->user->id_user,
                'id_want' => $want->id_want,
                'text' => 'User '.$this->_link(Auth::user()->login,'/user/'.Auth::user()->id_user).' send to '.$this->_link($want->user->login,'/user/'.$want->user->id_user).' card <a href="/details/' . $want->card->id_card . '">' . $want->card->name . '(#' . $want->card->number . '/' . $want->card->set->total_cards . ')</a>'
            ]);

            $msg = 'Seending <a href="/details/' . $want->card->id_card . '">' . $want->card->name . '(#' . $want->card->number . '/' . $want->card->set->total_cards . ')</a> from <a href="/user/' . $want->user_from->id_user . '">' . $want->user_from->login . '</a> to <a href"/user/' . $want->user->id_user . '">' . $want->user->login . '</a>';
            $from = User::find($want->id_user_from);
            $to = User::find($want->id_user);
            $this->dispatch(new AddMesssage($from, $msg, 4));
            $this->dispatch(new AddMesssage($to, $msg, 4));

            \DB::commit();
            return $this->_return("Send want success");
        } catch (CustomExecption $e){
            \DB::rollback();
            return $this->_returnError($e->getMessage(), $e);

        } catch (Exception $e) {
            \DB::rollback();
            return $this->_returnError("Send want fail", $e);
        }
    }

    public function remove($id_user_card)
    {
        try {
            UserCards::find($id_user_card)->delete();

            return $this->_return('Remove card', 'success');
        } catch (\Exception $e) {
            return $this->_returnError('Remove card fail', $e);
        }
    }

    public function completeTrade($id_want, Request $request){
        try {
            \DB::beginTransaction();

            $want = Want::find($id_want);
            $want->update(["id_status_want" => 3]);
            $valeu_pp = $want->pp;
            $want->user_from->increment("pp",$valeu_pp);
            $want->user->decrement("pp_reserve",$valeu_pp);


            $msg = 'Trade complete <a href="/details/' . $want->card->id_card . '">' . $want->card->name . '(#' . $want->card->number . '/' . $want->card->set->total_cards . ')</a> from <a href="/user/' . $want->user_from->id_user . '">' . $want->user_from->login . '</a> to <a href"/user/' . $want->user->id_user . '">' . $want->user->login . '</a>';;
            $this->dispatch(new AddMesssage(Auth::user(), $msg, 4));

            History::create([
                'id_user' => $want->user->id_user,
                'id_want' => $want->id_want,
                'text' => $msg
            ]);
            
            \DB::commit();
            return $this->_return('Complete success', 'success');
        } catch (Exception $e) {
            \DB::rollback();
            return $this->_returnError('Complete fail', $e);
        }
    }

    function update($id_user, Request $request){
        try {
            \DB::beginTransaction();

            User::find($id_user)->update($request->all());

            
            \DB::commit();
            return $this->_return('Update profile success','success');
        } catch (Exception $e) {
            \DB::rollback();
            return $this->_returnError('Update profile fail',$e);
            
        }
    }

    function reported($id_want, Request $request){
        try {
            \DB::beginTransaction();

            $update = [
                "id_status_want" => 4,
                "reason" => $request->reason
            ];

            $msg = "This card in alert: reason \"".$request->reason."\"";
            $this->dispatch(new AddMesssage(Auth::user(), $msg, 4));

            $want = Want::find($id_want);

            History::create([
                'id_user' => $want->user->id_user,
                'id_want' => $want->id_want,
                'text' => $msg
            ]);

            $want->update($update);

            
            \DB::commit();
            return $this->_return('Update profile success','success');
        } catch (Exception $e) {
            \DB::rollback();
            return $this->_returnError('Update profile fail',$e);
            
        }
    }

    // function removeWant($id_want){
    //     try {
    //         Want::find($id_want)->delete();
    //         return $this->_return('Update profile success','success');
    //     } catch (Exception $e) {
    //         return $this->_returnError('Update profile fail',$e);
    //     }
    // }
}
