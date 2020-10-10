<?php

namespace App\Utility;

class FuncUtility
{
    public static function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
                }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }

    public static function rand_with_weight($kv)
    {
        $sum = array_sum(array_values($kv));
        $rnd = (mt_rand() / mt_getrandmax()) * $sum;
        foreach ($kv as $k => $v)
        {
            if ($v >= $rnd)
                return $k;
            $rnd -= $v;
        }
        trigger_error('unreachable');
    }

}
