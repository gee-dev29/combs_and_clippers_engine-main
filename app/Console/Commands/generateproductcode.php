<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Str;

class generateproductcode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:generateProductCode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates product code for all existing products';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // retrieve all products
        $products = Product::all();

        if (!$products) {
            // show an error and exit if no product exists
            $this->error('No product exists');
            return;
        }

        // Print a warning
        $this->info('A new product code will be generated for all products');

        // ask for confirmation
        if (!$this->confirm('Do you wish to continue?')) return;

        foreach($products as $product){
            $product->update(['product_code'=>substr(Str::uuid(), 0, 6),]);
        }
        // show finish message
        $this->info('A new product code has been generated for all products');
    }
}
