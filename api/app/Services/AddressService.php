<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function getAddresses(User $user)
    {
        return $user->addresses()->get();
    }

    public function createAddress(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {
            $user = User::where('id', $user->id)
            ->lockForUpdate()
            ->firstOrFail();

            $hasAddresses = $user->addresses()->exists();

            $address = $user->addresses()->create($data);

            if(!$hasAddresses) {
                $address->is_default = true;
                $address->save();
            }

            return $address;  
        });
    }

    public function setDefaultAddress(User $user, int $addressId)
    {
        return DB::transaction(function() use ($user, $addressId){
             $user = User::where('id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $address = $user->addresses()
                ->where('id', $addressId)
                ->lockForUpdate()
                ->firstOrFail();

            $user->addresses()->update([
                'is_default' => false
            ]);

            $address->is_default = true;
    
            $address->save();

            return $address;
        });
    }
}