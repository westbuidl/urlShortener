<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public $items = null;
    public $totalQty = 0;
    public $totalPrice = 0;

    public function __construct($oldCart)
    {
        if ($oldCart){
            $this->items = $oldCart->items;
            $this->totalQty = $oldCart->totalQty;
            $this->totalPrice = $oldCart->totalPrice;

        }
    }

    public function add($item, $productID){
        $storedItem = ['qty' => 0,'price'=>$item->price, 'item'=> $item];
        if ($this->items) {
            if (array_key_exists($productID, $this->items)){
                $storedItem = $this->items['$productID'];
            }
        }
        $storedItem['qty']++;
        $storedItem['price'] = $item->price * $storedItem['qty'];
        $this->items['$productID'] = $storedItem;
        $this->totalQty++;
        $this->totalPrice += $item->price;
    }
}
