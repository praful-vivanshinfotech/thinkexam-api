<?php

namespace Database\Seeders;

use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RolesTableSeeder extends Seeder
{
    protected $role;

    /**
     * Create a new instance.
     *
     * @param RoleRepositoryInterface $role
     *
     * @return void
     */
    public function __construct(
        RoleRepositoryInterface $role
    ) {
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
            $roles = [
                [
                    'condition' => [
                        ['slug', '=', config('constant.SUPER_ADMIN_ROLE_SLUG')],
                    ],
                    'attributes' => [
                        'name' => config('constant.SUPER_ADMIN_ROLE_LABEL'),
                        'slug' => config('constant.SUPER_ADMIN_ROLE_SLUG'),
                    ],
                ],
                [
                    'condition' => [
                        ['slug', '=', config('constant.ADMIN_ROLE_SLUG')],
                    ],
                    'attributes' => [
                        'name' => config('constant.ADMIN_ROLE_LABEL'),
                        'slug' => config('constant.ADMIN_ROLE_SLUG'),
                    ],
                ],
            ];
            foreach ($roles as $_role) {
                $this->role->createOrUpdateByWhereCondition($_role['condition'], $_role['attributes']);
            }
            Log::info(__METHOD__ . " | Success: Seeding completed successfully.");
        } catch (\Exception $e) {
            Log::error(__METHOD__ . " | Error: {$e->getMessage()}");
        }
    }
}
