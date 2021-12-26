<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use \Cart as Cart;
use Validator;
use Log;

class CartController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cart');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $duplicates = Cart::search(function ($cartItem, $rowId) use ($request) {
            return $cartItem->id === $request->id;
        });

        if (!$duplicates->isEmpty()) {
            return redirect('cart')->withSuccessMessage('Item is already in your cart!');
        }

        Cart::add($request->id, $request->name, 1, $request->price)->associate('App\Product');
        return redirect('cart')->withSuccessMessage('Item was added to your cart!');
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validation on max quantity
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|between:1,5'
        ]);

         if ($validator->fails()) {
            session()->flash('error_message', 'Quantity must be between 1 and 5.');
            return response()->json(['success' => false]);
         }

        Cart::update($id, $request->quantity);
        session()->flash('success_message', 'Quantity was updated successfully!');

        return response()->json(['success' => true]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Cart::remove($id);
        return redirect('cart')->withSuccessMessage('Item has been removed!');
    }

    /**
     * Remove the resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function emptyCart()
    {
        Cart::destroy();
        return redirect('cart')->withSuccessMessage('Your cart has been cleared!');
    }

    /**
     * Switch item from shopping cart to wishlist.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function switchToWishlist($id)
    {
        $item = Cart::get($id);

        Cart::remove($id);

        $duplicates = Cart::instance('wishlist')->search(function ($cartItem, $rowId) use ($id) {
            return $cartItem->id === $id;
        });

        if (!$duplicates->isEmpty()) {
            return redirect('cart')->withSuccessMessage('Item is already in your Wishlist!');
        }

        Cart::instance('wishlist')->add($item->id, $item->name, 1, $item->price)
                                  ->associate('App\Product');

        return redirect('cart')->withSuccessMessage('Item has been moved to your Wishlist!');

    }


    public function discount(Request $request)
    {
       
        echo ('hidayat');
        //echo 'console.log('. json_encode( $request->coupon ) .')';
        $coupon = $request->coupon;
        $total = Cart::total()*0.9;
        //return view('cart')->with('total_disc',$total);
        if ($coupon=='FA111') {
            return view('cart')->with('total_disx',$total);
            //return redirect('cart')->withSuccessMessage($total);
        } else if ($coupon=='FA222') {
            $idPrice=0;
            //return view('cart')->with('total_disx',$total);
            //$citems = Cart::search(function ($cartItem, $rowId) {return $cartItem->name === "Playstation 4";});
            //if (!$duplicates->isEmpty()) {
            foreach (Cart::content() as $row) {
                if ($row->code === "FA4532" ) {
                    $idCart = $row->id;
                    $idPrice = $row->subtotal-50;
                }
            }
           if ($idPrice==0) {
            $idPrice=Cart::total();
           }
            //return redirect('cart')->withSuccessMessage($idPrice);
            return view('cart')->with('total_disx',$idPrice-50);
        } else if ($coupon=='FA333') {
            $idPrice = 0;
            $i=0;
            //return view('cart')->with('total_disx',$total);
            $citems = Cart::search(function ($cartItem, $rowId) {return $cartItem->name === "Playstation 4";});
            //if (!$duplicates->isEmpty()) {
            foreach (Cart::content() as $row) {
                if ($row->price > 400) {
                    $iIndP = $row->subtotal;
                    $idCart = $row->id;
                    $idPrice = $idPrice+$iIndP;
                    $i = $i+1;
                    Log::info($idPrice);
                }
            }
            //return redirect('cart')->withSuccessMessage($idPrice);
            return view('cart')->with('total_disx',$idPrice*.94);
        }


        else {
            return redirect('cart')->withSuccessMessage('wrong coupon');
        }
        

    }

     public function getDiscount()
    {
       
        //return view('cart')->with('total_disc','123');

        return view('cart', ['total_disx' => Cart::total()]);

    }

    
}
