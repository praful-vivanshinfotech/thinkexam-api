<?php

namespace Database\Seeders;

use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsersTableSeeder extends Seeder
{
    protected $user, $role;

    /**
     * Create a new instance.
     *
     * @param UserRepositoryInterface $user
     * @param RoleRepositoryInterface $role
     *
     * @return void
     */
    public function __construct(
        UserRepositoryInterface $user,
        RoleRepositoryInterface $role
    ) {
        $this->user = $user;
        $this->role = $role;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $user = [
                'condition' => [
                    ['email', '=', config('constant.SUPER_ADMIN_EMAIL_ADDRESS')],
                ],
                'attributes' => [
                    'email' => config('constant.SUPER_ADMIN_EMAIL_ADDRESS'),
                    'first_name' => config('constant.SUPER_ADMIN_FIRST_NAME'),
                    'phone_number' => config('constant.SUPER_ADMIN_PHONE_NUMBER'),
                    'password' => config('constant.SUPER_ADMIN_PASSWORD'),
                    'role_id' => config('constant.SUPER_ADMIN_ROLE_ID'),
                    'email_verified_at' => now(),
                ],
            ];

            // Start DB trasaction to create/update user and sync the role
            DB::transaction(function () use ($user) {
                $user = $this->user->createOrUpdateByWhereCondition($user['condition'], $user['attributes']);
            });
            Log::info(__METHOD__ . " | Success: Seeding completed successfully.");
        } catch (\Exception $e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
        }
    }
}
