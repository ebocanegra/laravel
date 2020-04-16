<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

// Necesitaremos el modelo Avion para ciertas tareas.
use App\Avion;
use App\Fabricante;

class AvionController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	public function index(Request $request)
    {
        // Devolverá todos los fabricantes.
        // return "Mostrando todos los fabricantes de la base de datos.";
        // return Fabricante::all();  No es lo más correcto por que se devolverían todos los registros. Se recomienda usar Filtros.
        // Se debería devolver un objeto con una propiedad como mínimo data y el array de resultados en esa propiedad.
        // A su vez también es necesario devolver el código HTTP de la respuesta.
        // php http://elbauldelprogramador.com/buenas-practicas-para-el-diseno-de-una-api-RESTful-pragmatica/
        // https://cloud.google.com/storage/docs/json_api/v1/status-codes
        
      
        $consulta = Avion::query();

        // El formato utilizado para las solicitudes de filtrado es /aviones?sort=model,-velocidad    
        // debemos procesar todos los parámetros pasados. 
        
        if ($request->filled('sort'))
        {

          $CamposOrdenacion = array_filter(explode (',', $request->input('sort','')));        

          if (!(empty($CamposOrdenacion)))
          {         
        
            foreach ($CamposOrdenacion as $campo) {                
               $sentidoOrdenacion = Str::startsWith($campo,'-')? 'desc' : 'asc';
               $NombreCampo = ltrim($campo,'-');          
               $consulta->orderBy($NombreCampo,$sentidoOrdenacion);
           }
          }
        }   

        // El formato utilizado para las solicitudes de filtrado es /aviones?filter=model:Falcon,velocidad=12
        if ($request->filled('filter'))
        {

            $CamposFiltrados = array_filter(explode (',', $request->input('filter','')));        
            foreach ($CamposFiltrados as $campoFiltro)
            {
                [$criterio,$valor] = explode(':',$campoFiltro);

                // FDGA 31/03/2020 La sentencia ->where hace una comparaciónde igualdad con los campos con lo que si
                // queremos una búsqueda por LIKE deberemos personalizarla. En la siguiente
                // instrucción hacemos que podamos buscar por LIKE en el modelo
                if ($criterio=='modelo')
                {$consulta->where($criterio,'LIKE', '%'.$valor.'%');}
                else
                {$consulta->where($criterio, $valor);}

            }
        
        }
        return response()->json(['status'=>'ok','data'=>$consulta->get()], 200)
                         ->header('X-Saludos-de-DAVID',1000);
	}
	
	
    public function store (Request $request){


		// Primero comprobaremos si estamos recibiendo todos los campos.
		if ( !$request->input('modelo') || !$request->input('longitud') || !$request->input('capacidad') || !$request->input('velocidad') || !$request->input('alcance') || !$request->input('fabricante_id') )
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
			return response()->json(['errors'=>array(['code'=>422,'message'=>'Faltan datos necesarios para el proceso de alta.'])],422);
		}

		// Buscamos el Fabricante.
		$idFabricante=$request->input('fabricante_id');
		$fabricante= Fabricante::find($idFabricante);

		// Si el fabricante existe entonces lo almacenamos.
		// Insertamos una fila en Aviones con create pasándole todos los datos recibidos.
		$nuevoAvion=$fabricante->aviones()->create($request->all());

        // Más información sobre respuestas en http://jsonapi.org/format/
        // Devolvemos el código HTTP 201 Created – [Creada] Respuesta a un POST que resulta en una creación. Debería ser combinado con un encabezado Location, apuntando a la ubicación del nuevo recurso.
        return response()->json(['data'=>$nuevoAvion], 201)->header('Location',  url('/api/v1/').'/aviones/'.$nuevoAvion->serie);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
		// return "Se muestra Fabricante con id: $id";
		// Buscamos un fabricante por el id.
		$avion=Avion::find($id);

		// Si no existe ese avion devolvemos un error.
		if (!$avion)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un avión con ese código.'])],404);
		}

		return response()->json(['status'=>'ok','data'=>$avion],200);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		// El fabricante existe entonces buscamos el avion que queremos editar asociado a ese fabricante.
		$avion = Avion::find($id);

		// Si no existe ese avión devolvemos un error.
		if (!$avion)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 404.
			// En code podríamos indicar un código de error personalizado de nuestra aplicación si lo deseamos.
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un avión con ese código asociado a ese fabricante.'])],404);
		}	


		// Listado de campos recibidos teóricamente.
		$modelo=$request->input('modelo');
		$longitud=$request->input('longitud');
		$capacidad=$request->input('capacidad');
		$velocidad=$request->input('velocidad');
		$alcance=$request->input('alcance');
		$idFabricante=$request->input('fabricante_id');

		// Necesitamos detectar si estamos recibiendo una petición PUT o PATCH.
		// El método de la petición se sabe a través de $request->method();
		/*	Modelo		Longitud		Capacidad		Velocidad		Alcance */
		if ($request->method() === 'PATCH')
		{
			// Creamos una bandera para controlar si se ha modificado algún dato en el método PATCH.
			$bandera = false;

			// Actualización parcial de campos.
			if ($modelo != null && $modelo!='')
			{
				$avion->modelo = $modelo;
				$bandera=true;
			}

			if ($longitud != null && $longitud!='')
			{
				$avion->longitud = $longitud;
				$bandera=true;
			}

			if ($capacidad != null && $capacidad!='')
			{
				$avion->capacidad = $capacidad;
				$bandera=true;
			}

			if ($velocidad != null && $velocidad!='')
			{
				$avion->velocidad = $velocidad;
				$bandera=true;
			}

			if ($alcance != null && $alcance!='')
			{
				$avion->alcance = $alcance;
				$bandera=true;
			}
			if ($idFabricante != null && $idFabricante!='')
			{
				$avion->fabricante_id = $idFabricante;
				$bandera=true;
			}

			if ($bandera)
			{
				// Almacenamos en la base de datos el registro.
				$avion->save();
				return response()->json(['status'=>'ok','data'=>$avion], 200);
			}
			else
			{
				// Devolveremos un código 304 Not Modified – [No Modificada] Usado cuando el cacheo de encabezados HTTP está activo
				// Este código 304 no devuelve ningún body, así que si quisiéramos que se mostrara el mensaje usaríamos un código 200 en su lugar.
				return response()->json(['code'=>304,'message'=>'No se ha modificado ningún dato de fabricante.'],304);
			}

		}

		// Si el método no es PATCH entonces es PUT y tendremos que actualizar todos los datos.
		if (!$modelo || !$longitud || !$capacidad || !$velocidad || !$alcance || !$idFabricante)
		{
			// Se devuelve un array errors con los errores encontrados y cabecera HTTP 422 Unprocessable Entity – [Entidad improcesable] Utilizada para errores de validación.
			return response()->json(['errors'=>array(['code'=>422,'message'=>'Faltan valores para completar el procesamiento.'])],422);
		}

		$avion->modelo = $modelo;
		$avion->longitud = $longitud;
		$avion->capacidad = $capacidad;
		$avion->velocidad = $velocidad;
		$avion->alcance = $alcance;
		$avion->fabricante_id = $idFabricante;

		// Almacenamos en la base de datos el registro.
		$avion->save();

		return response()->json(['data'=>$avion], 200);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy(Request $request, $id)
	{
		$avion=Avion::find($id);

		if(!$avion){
			return response()->json(['errors'=>array(['code'=>404,'message'=>'No se encuentra un avion con ese codigo de fabricante.'])],404);
		}

		$avion->delete();
		return response()->json(['errors'=>array(['code'=>204,'message'=>'Se ha eliminado el avion correctamente.'])],204);
		
		

	}

}