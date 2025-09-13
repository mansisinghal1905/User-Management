<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserRepository
{
    protected $cacheTime = 60; 

    public function getAll()
    {
        return Cache::remember('users.all', $this->cacheTime, function () {
            return User::all();
        });
    }

    public function findById($id)
    {
        return Cache::remember("user.{$id}", $this->cacheTime, function () use ($id) {
            return User::find($id);
        });
    }

    public function findByEmail($email)
    {
        return Cache::remember("user.email.{$email}", $this->cacheTime, function () use ($email) {
            return User::where('email', $email)->first();
        });
    }

    public function create(array $data)
    {
        $user = User::create($data);
        $this->clearCache();
        return $user;
    }

    public function update(User $user, array $data)
    {
        $user->update($data);
        $this->clearCache();
        $this->clearUserCache($user->id);
        return $user;
    }

    public function delete(User $user)
    {
        $user->delete();
        $this->clearCache();
        $this->clearUserCache($user->id);
    }

    protected function clearCache()
    {
        Cache::forget('users.all');
    }

    protected function clearUserCache($userId)
    {
        Cache::forget("user.{$userId}");
        $user = User::find($userId);
        if ($user) {
            Cache::forget("user.email.{$user->email}");
        }
    }
}