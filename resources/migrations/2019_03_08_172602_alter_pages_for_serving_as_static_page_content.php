<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use TCG\Voyager\Models\Page;

class AlterPagesForServingAsStaticPageContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->renameColumn('slug', 'uri');
            $table->string('group', 50)->nullable();
            $table->string('view', 50)->nullable();
            $table->boolean('status')->default(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->renameColumn('uri', 'slug');
        });
    }
}
