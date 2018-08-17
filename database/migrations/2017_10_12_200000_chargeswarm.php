<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Chargeswarm extends Migration
{
    /**
     * Create the subscription table
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chargebee_subscriptions', function($table) {
            $table->increments('id');
            $table->string('subscription_id');
            $table->string('plan_id');
            $table->integer('model_id');
            $table->string('model_type');
            $table->integer('billing_period')->default(1);
            $table->enum('billing_period_unit', ['year', 'month', 'week'])->default('month');
            $table->integer('plan_quantity')->default(1);
            $table->integer('plan_free_quantity')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_starts_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('next_billing_at')->nullable();
            $table->enum('status', ['future', 'in_trial', 'active', 'non_renewing', 'paused', 'cancelled'])->default('in_trial');
            $table->integer('last_four')->nullable();
            $table->timestamps();
        });

        Schema::create('chargebee_subscriptions_usages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subscription_id');
            $table->string('metadata_id');
            $table->float('used', 9, 2)->default(0);
            $table->float('total', 9, 2)->default(0);
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
        Schema::dropIfExists('chargebee_subscriptions');
        Schema::dropIfExists('chargebee_subscriptions_usages');
    }
}
