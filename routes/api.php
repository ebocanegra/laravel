<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Versionado de la API.
Route::prefix('v1')->group(function () {

    // resource recibe nos parámetros(URI del recurso, Controlador que gestionará las peticiones)
    Route::resource('fabricantes','FabricanteController',['except'=>['edit','create'] ]);   // Todos los métodos menos Edit que mostraría un formulario de edición.

    // Si queremos dar  la funcionalidad de ver todos los aviones tendremos que crear una ruta específica.
    // Pero de aviones solamente necesitamos solamente los métodos index y show.
    // Lo correcto sería hacerlo así:
    Route::resource('aviones','AvionController'); // El resto se gestionan en FabricanteAvionController

    // Como la clase principal es fabricantes y un avión no se puede crear si no le indicamos el fabricante,
    // entonces necesitaremos crear lo que se conoce como  "Recurso Anidado" de fabricantes con aviones.
    // Definición del recurso anidado:
    Route::resource('fabricantes.aviones','FabricanteAvionController',[ 'except'=>['show','edit','create'] ]);
});


/*
php artisan route:list

+--------+-----------+--------------------------------------------------+-----------------------------+--------------------------------------------------------+--------------+
| Domain | Method    | URI                                              | Name                        | Action                                                 | Middleware   |
+--------+-----------+--------------------------------------------------+-----------------------------+--------------------------------------------------------+--------------+
|        | GET|HEAD  | /                                                |                             | Closure                                                | web          |
|        | GET|HEAD  | api/user                                         |                             | Closure                                                | api,auth:api |
|        | GET|HEAD  | api/v1/aviones                                   | aviones.index               | App\Http\Controllers\AvionController@index             | api          |
|        | GET|HEAD  | api/v1/aviones/{avione}                          | aviones.show                | App\Http\Controllers\AvionController@show              | api          |
|        | GET|HEAD  | api/v1/fabricantes                               | fabricantes.index           | App\Http\Controllers\FabricanteController@index        | api          |
|        | POST      | api/v1/fabricantes                               | fabricantes.store           | App\Http\Controllers\FabricanteController@store        | api          |
|        | GET|HEAD  | api/v1/fabricantes/{fabricante}                  | fabricantes.show            | App\Http\Controllers\FabricanteController@show         | api          |
|        | PUT|PATCH | api/v1/fabricantes/{fabricante}                  | fabricantes.update          | App\Http\Controllers\FabricanteController@update       | api          |
|        | DELETE    | api/v1/fabricantes/{fabricante}                  | fabricantes.destroy         | App\Http\Controllers\FabricanteController@destroy      | api          |
|        | GET|HEAD  | api/v1/fabricantes/{fabricante}/aviones          | fabricantes.aviones.index   | App\Http\Controllers\FabricanteAvionController@index   | api          |
|        | POST      | api/v1/fabricantes/{fabricante}/aviones          | fabricantes.aviones.store   | App\Http\Controllers\FabricanteAvionController@store   | api          |
|        | PUT|PATCH | api/v1/fabricantes/{fabricante}/aviones/{avione} | fabricantes.aviones.update  | App\Http\Controllers\FabricanteAvionController@update  | api          |
|        | DELETE    | api/v1/fabricantes/{fabricante}/aviones/{avione} | fabricantes.aviones.destroy | App\Http\Controllers\FabricanteAvionController@destroy | api          |
+--------+-----------+--------------------------------------------------+-----------------------------+--------------------------------------------------------+--------------+

*/