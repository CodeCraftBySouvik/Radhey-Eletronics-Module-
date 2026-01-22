<?php

namespace App\Providers;

use App\Interfaces\CategoryInterface;
use App\Interfaces\SubcategoryInterface;
use App\Interfaces\UserInterface;
use App\Interfaces\ProductInterface;
// use App\Interfaces\PurchaseOrderInterface;
use App\Interfaces\StoreInterface;
use App\Interfaces\OrderInterface;
use App\Interfaces\CartInterface;
use App\Interfaces\PaymentCollectionInterface;
use App\Interfaces\CustomerInterface;

use App\Repositories\CategoryRepository;
use App\Repositories\SubcategoryRepository;
use App\Repositories\UserRepository;
use App\Repositories\ProductRepository;
// use App\Repositories\PurchaseOrderRepository;
use App\Repositories\StoreRepository;
use App\Repositories\OrderRepository;
use App\Repositories\CartRepository;
use App\Repositories\PaymentCollectionRepository;
use App\Repositories\CustomerRepository;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CategoryInterface::class, CategoryRepository::class);
        $this->app->bind(SubcategoryInterface::class, SubcategoryRepository::class);        
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(ProductInterface::class, ProductRepository::class);
        // $this->app->bind(PurchaseOrderInterface::class, PurchaseOrderRepository::class);        
        $this->app->bind(StoreInterface::class, StoreRepository::class);
        $this->app->bind(OrderInterface::class, OrderRepository::class);        
        $this->app->bind(CartInterface::class, CartRepository::class);
        $this->app->bind(PaymentCollectionInterface::class, PaymentCollectionRepository::class);
        $this->app->bind(CustomerInterface::class, CustomerRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
