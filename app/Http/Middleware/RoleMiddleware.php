<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Map route names to their required permissions for kasir role.
     * Admin and Master bypass this entirely.
     */
    private function getRoutePermissionMap(): array
    {
        return [
            // Products
            'products.index'            => 'products.view',
            'products.show'             => 'products.show',
            'products.create'           => 'products.create',
            'products.store'            => 'products.create',
            'products.edit'             => 'products.edit',
            'products.update'           => 'products.edit',
            'products.destroy'          => 'products.delete',
            'products.adjust-stock'     => 'products.stock',
            'products.adjustment-history' => 'products.stock',
            'products.barcode-generator' => 'products.barcode',
            'products.print-barcode'    => 'products.barcode',
            'products.print-barcodes'   => 'products.barcode',
            'api.generate-barcode'      => 'products.barcode',

            // Categories
            'categories.index'          => 'categories.view',
            'categories.show'           => 'categories.show',
            'categories.create'         => 'categories.create',
            'categories.store'          => 'categories.create',
            'categories.edit'           => 'categories.edit',
            'categories.update'         => 'categories.edit',
            'categories.destroy'        => 'categories.delete',

            // Students
            'students.index'            => 'students.view',
            'students.show'             => 'students.show',
            'students.create'           => 'students.create',
            'students.store'            => 'students.create',
            'students.edit'             => 'students.edit',
            'students.update'           => 'students.edit',
            'students.destroy'          => 'students.delete',
            'students.rfid.register'    => 'students.edit',
            'students.rfid.store'       => 'students.edit',
            'api.generate-rfid'         => 'students.create',
            'students.card.generate'    => 'students.view',
            'students.cards.batch'      => 'students.view',
            'students.rfid.export'      => 'students.view',

            // Teachers
            'teachers.index'            => 'teachers.view',
            'teachers.show'             => 'teachers.show',
            'teachers.create'           => 'teachers.create',
            'teachers.store'            => 'teachers.create',
            'teachers.edit'             => 'teachers.edit',
            'teachers.update'           => 'teachers.edit',
            'teachers.destroy'          => 'teachers.delete',
            'teachers.rfid.register'    => 'teachers.edit',
            'teachers.rfid.store'       => 'teachers.edit',
            'api.generate-teacher-rfid' => 'teachers.create',

            // POS
            'kasir.pos.index'           => 'pos.access',
            'kasir.pos.api.products'    => 'pos.access',
            'kasir.pos.api.barcode'     => 'pos.access',
            'kasir.pos.api.rfid'        => 'pos.access',
            'kasir.pos.api.search'      => 'pos.access',
            'kasir.pos.api.teacher-rfid' => 'pos.access',
            'kasir.pos.api.teacher-search' => 'pos.access',
            'kasir.pos.api.recent-sales' => 'pos.access',
            'kasir.pos.api.checkout'    => 'pos.access',
            'kasir.pos.receipt'         => 'pos.access',
            'api.stock-monitor'         => 'pos.access',

            // Kasir Specific Resources (Duplicated from Admin)
            'kasir.categories.index'    => 'categories.view',
            'kasir.categories.show'     => 'categories.show',
            'kasir.categories.create'   => 'categories.create',
            'kasir.categories.store'    => 'categories.create',
            'kasir.categories.edit'     => 'categories.edit',
            'kasir.categories.update'   => 'categories.edit',
            'kasir.categories.destroy'  => 'categories.delete',

            'kasir.products.index'      => 'products.view',
            'kasir.products.show'       => 'products.show',
            'kasir.products.create'     => 'products.create',
            'kasir.products.store'      => 'products.create',
            'kasir.products.edit'       => 'products.edit',
            'kasir.products.update'     => 'products.edit',
            'kasir.products.destroy'    => 'products.delete',
            
            'kasir.products.adjust-stock' => 'products.stock',
            'kasir.products.adjustment-history' => 'products.stock',
            'kasir.products.barcode-generator' => 'products.barcode',
            'kasir.products.print-barcode' => 'products.barcode',
            'kasir.products.print-barcodes' => 'products.barcode',
            'kasir.api.generate-barcode' => 'products.barcode',
            
            'kasir.stock-adjustments.update' => 'reports.stock_adjustments.edit',
            'kasir.stock-adjustments.destroy' => 'reports.stock_adjustments.delete',
            
            'kasir.stock-ins.index'     => 'stock_ins.view',
            'kasir.stock-ins.create'    => 'stock_ins.create',
            'kasir.stock-ins.store'     => 'stock_ins.create',
            'kasir.stock-ins.show'      => 'stock_ins.show',
            'kasir.stock-ins.destroy'   => 'stock_ins.delete',

            'kasir.vouchers.index'      => 'vouchers.view',
            'kasir.vouchers.create'     => 'vouchers.create',
            'kasir.vouchers.store'      => 'vouchers.create',
            'kasir.vouchers.show'       => 'vouchers.show',
            'kasir.vouchers.edit'       => 'vouchers.edit',
            'kasir.vouchers.update'     => 'vouchers.edit', 
            'kasir.vouchers.destroy'    => 'vouchers.delete',
            'kasir.vouchers.redeem'     => 'vouchers.redeem',
            'kasir.vouchers.redeem.form'=> 'vouchers.redeem',
            'kasir.vouchers.print'      => 'vouchers.print',
            'kasir.vouchers.print.batch'=> 'vouchers.print',
            'kasir.vouchers.search.student' => 'vouchers.redeem',

            'kasir.savings.index'       => 'savings.view',
            'kasir.savings.create'      => 'savings.manage',
            'kasir.savings.store'       => 'savings.manage',
            'kasir.savings.show'        => 'savings.show',
            'kasir.savings.deposit'     => 'savings.manage',
            'kasir.savings.withdraw'    => 'savings.manage',

            'kasir.transactions.index'  => 'transactions.history',
            'kasir.transactions.student' => 'transactions.history',
            'kasir.transactions.topup.form' => 'transactions.topup',
            'kasir.transactions.topup'  => 'transactions.topup',
            
            'kasir.students.index'      => 'students.view',
            'kasir.students.show'       => 'students.show',
            'kasir.students.create'     => 'students.create',
            'kasir.students.store'      => 'students.create',
            'kasir.students.edit'       => 'students.edit',
            'kasir.students.update'     => 'students.edit',
            'kasir.students.destroy'    => 'students.delete',
            'kasir.students.rfid.register' => 'students.edit',
            'kasir.students.rfid.store' => 'students.edit',
            'kasir.api.generate-rfid'   => 'students.create',
            'kasir.students.card.generate' => 'students.view',
            'kasir.students.cards.batch' => 'students.view',
            'kasir.students.rfid.export'=> 'students.view',
            
            'kasir.teachers.index'      => 'teachers.view',
            'kasir.teachers.show'       => 'teachers.show',
            'kasir.teachers.create'     => 'teachers.create',
            'kasir.teachers.store'      => 'teachers.create',
            'kasir.teachers.edit'       => 'teachers.edit',
            'kasir.teachers.update'     => 'teachers.edit',
            'kasir.teachers.destroy'    => 'teachers.delete',
            'kasir.teachers.rfid.register' => 'teachers.edit',
            'kasir.teachers.rfid.store' => 'teachers.edit',
            'kasir.api.generate-teacher-rfid' => 'teachers.create',
            
            // ... add others if needed

            // Transactions
            'transactions.index'        => 'transactions.history',
            'transactions.student'      => 'transactions.history',
            'kasir.pos.transactions-history' => 'transactions.history',
            'kasir.pos.api.void'        => 'transactions.void',
            'transactions.topup.form'   => 'transactions.topup',
            'transactions.topup'        => 'transactions.topup',
            'transactions.search.student' => 'pos.access',
            'transactions.search.teacher' => 'pos.access',

            // Reports
            // Reports
            'reports.sales'              => 'reports.sales',
            'reports.sales.export'       => 'reports.export',
            'reports.inventory'          => 'reports.inventory',
            'reports.inventory.export'   => 'reports.export',
            'reports.financial'          => 'reports.financial',
            'reports.financial.export'   => 'reports.export',
            'reports.student-transactions' => 'reports.student_transactions',
            'reports.student-transactions.export' => 'reports.export',
            'reports.stock-adjustments'  => 'reports.stock_adjustments',
            'reports.stock-adjustments.export' => 'reports.export',
            'stock-adjustments.update'   => 'reports.stock_adjustments.edit',
            'stock-adjustments.destroy'  => 'reports.stock_adjustments.delete',

            // Users
            'users.index'               => 'users.view',
            'users.show'                => 'users.show',
            'users.create'              => 'users.create',
            'users.store'               => 'users.create',
            'users.edit'                => 'users.edit',
            'users.update'              => 'users.edit',
            'users.destroy'             => 'users.delete',

            // Stock Ins
            'stock-ins.index'           => 'stock_ins.view',
            'stock-ins.show'            => 'stock_ins.show',
            'stock-ins.create'          => 'stock_ins.create',
            'stock-ins.store'           => 'stock_ins.create',
            'stock-ins.edit'            => 'stock_ins.edit',
            'stock-ins.update'          => 'stock_ins.edit',
            'stock-ins.destroy'         => 'stock_ins.delete',

            // Expenses
            'expenses.index'            => 'expenses.view',
            'expenses.show'             => 'expenses.show',
            'expenses.create'           => 'expenses.create',
            'expenses.store'            => 'expenses.create',
            'expenses.edit'             => 'expenses.edit',
            'expenses.update'           => 'expenses.edit',
            'expenses.destroy'          => 'expenses.delete',

            // Vouchers
            'vouchers.index'            => 'vouchers.view',
            'vouchers.show'             => 'vouchers.show',
            'vouchers.create'           => 'vouchers.create',
            'vouchers.store'            => 'vouchers.create',
            'vouchers.edit'             => 'vouchers.edit',
            'vouchers.update'           => 'vouchers.edit',
            'vouchers.destroy'          => 'vouchers.delete',
            'vouchers.search.student'   => 'vouchers.redeem',
            'vouchers.print'            => 'vouchers.print',
            'vouchers.print.batch'      => 'vouchers.print',
            'vouchers.redeem.form'      => 'vouchers.redeem',
            'vouchers.redeem'           => 'vouchers.redeem',

            // Savings
            'savings.index'             => 'savings.view',
            'savings.create'            => 'savings.manage',
            'savings.store'             => 'savings.manage',
            'savings.show'              => 'savings.show',
            'savings.deposit'           => 'savings.manage',
            'savings.withdraw'          => 'savings.manage',
        ];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect('/login');
        }

        $user = $request->user();

        // Hanya Master yang selalu bisa akses tanpa batas
        if ($user->role === 'master') {
            return $next($request);
        }

        // Check apakah role user ada dalam daftar roles yang diizinkan (e.g. 'role:admin,master,kasir')
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized action.');
        }

        // Untuk kasir dan admin, cek permission granular berdasarkan route name
        if (in_array($user->role, ['kasir', 'admin'])) {
            $routeName = $request->route()->getName();
            $permissionMap = $this->getRoutePermissionMap();

            if (isset($permissionMap[$routeName])) {
                $requiredPermission = $permissionMap[$routeName];
                if (!$user->hasPermission($requiredPermission)) {
                    abort(403, 'Anda tidak memiliki akses untuk fitur ini.');
                }
            }
            // If route is not in the map, allow access (e.g., dashboard, profile)
        }

        return $next($request);
    }
}
