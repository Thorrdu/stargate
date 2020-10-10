<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TradeResource extends Model
{
    public function trade()
    {
        return $this->belongsTo('App\Trade');
    }

    public function unit()
    {
        return $this->belongsTo('App\Unit');
    }

    public function setValue()
    {
        try
        {
            $this->trade_value = 0;
            if(!is_null($this->unit_id))
            {
                $unitPrice = $this->unit->getPrice($this->quantity);
                foreach($unitPrice as $resource => $price)
                {
                    switch ($resource)
                    {
                        case 'iron':
                            $this->trade_value += $price;
                        break;
                        case 'gold':
                            $this->trade_value += $price;
                        break;
                        case 'quartz':
                        case 'naqahdah':
                            $this->trade_value += $price;
                        break;
                    }
                }
                return;
            }

            switch ($this->resource)
            {
                case 'iron':
                    $this->trade_value = $this->quantity;
                break;
                case 'gold':
                    $this->trade_value = $this->quantity * 1.5;
                break;
                case 'quartz':
                case 'naqahdah':
                    $this->trade_value = $this->quantity * 3;
                break;
                case 'military':
                    $this->trade_value = $this->quantity * 0.2;
                break;
                case 'E2PZ':
                    $this->trade_value = $this->quantity * 2500;
                break;
            }

        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }
}
