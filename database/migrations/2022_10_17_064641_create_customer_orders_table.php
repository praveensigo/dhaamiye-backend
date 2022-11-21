<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    return new class extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('customer_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->references('id')->on('customers');
                $table->foreignId('driver_id')->references('id')->on('drivers')->nullable();
                $table->foreignId('truck_id')->references('id')->on('trucks')->nullable();
                $table->foreignId('fuel_station_id')->references('id')->on('fuel_stations');
                $table->integer('order_type')->default(1)->comment('1:Normal, 2:Schedule delivery ');            
                $table->decimal('fuel_quantity_price',12,2);
                $table->decimal('tax',12,2)->nullable();
                $table->decimal('delivery_charge', 12, 2);
                $table->string('coupon_code')->nullable();
                $table->decimal('promotion_discount',12,2)->default(0);
                $table->decimal('other_charges',12,2)->default(0);
                $table->decimal('total',12,2);
                $table->decimal('amount_commission',12,2)->nullable();
                $table->decimal('delivery_charge_commission',12,2)->nullable();
                $table->decimal('total_commission',12,2)->nullable();
                $table->date('delivery_date')->nullable();
                $table->time('delivery_time')->nullable();
                $table->integer('pin')->nullable();
                $table->integer('status')->default(0)->comment('0:Requested 1:Pending, 2:Accepted, 3:Ongoing, 4:Scheduled, 5:Delivered, 6:Cancelled, 7:Missed');
                $table->timestamp('accepted_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->text('cancel_reason')->nullable();
                $table->integer('added_by')->nullable()->comment('1:Admin, 2:Sub admin 3:Customer 5:fuel station');
                $table->foreignId('added_user')->nullable()->references('id')->on('users');
                $table->timestamps();
            });
        }
    
        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('customer_orders');
        }
    };
    
   