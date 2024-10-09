<?php
abstract class DataCharge{
    private $_route;

    public function __construct($route){
        $this->_route = $route;
    }

    public abstract function CheckFormat($Line);

    public abstract function CorrectLine($Line);

    public function ReadData(){
    
        $GoodFormatLines = [];
        $i = 0;

        $file = fopen($this->_route, 'r');
        while (($line = fgetcsv($file, 0 ,";")) !== FALSE) {

            if ($i == 0){
                print_r($line);
                $i=1;
            }

            $result = $this->CheckFormat($line);

            if (!$result){
                $result = $this->CorrectLine($line);
            }

            if ($result){
                $GoodFormatLines[] = $line;
            }
        }
        fclose($file);

return $GoodFormatLines;
    }
}

class AsignaturesChecker extends DataCharge{
    public function CheckFormat($Line){
        //retorno false cuando id es null (ahi lo borro) .1
        //cuando asignatura no es un string (lo marco como null) .2
        //cuando nivel no es un int (lo borro) .4
        $cond1 = $Line[1] != null;
        $cond2 = is_string($Line[2]);
        $cond3 = is_numeric($Line[3]);

        return $cond1 && $cond2 && $cond3;
    }

    public function CorrectLine($line){
        $GoodLines = [];


        if (!is_string($line[2])){
            $line[2] = null;
            $GoodLines[] = $line;
            return true;
        }
        return false;
    }
}

class StudentsChecker extends DataCharge{
    public function CheckFormat($Line){
        //0 -Codigo plan no me importa por ahora
        //1 -Carrera no me importa por ahora
        //2 -COHORTE null, se borra y debe respetar formato YYYY-SS
        //3 -NUMERO DE ESTUDIANTE null, se borra
        //4 -Bloqueo va entre S y N, si es null no me importa
        //5 -Causal bloqueo es un string
        //6 -RUN no puede ser null
        //7 -DV largo 1 no null
        //Todo sobre nombres no me importa, excepto Nombres y apellido paterno
        //Logro en si no me importa
        //Fecha logro y logro no nulo
	    //Ultima carga no me importa
        $formatoFecha = '/^\d{4}-\d{2}$/';

        $cond1 = preg_match($formatoFecha, $Line[2]) && $Line[2] != null;
        $cond2 = $Line[3] != null;
        $cond3 = $Line[4] == "S" || $Line[4] == "N";
        $cond4 = strlen($Line[6]) == 8 and $Line[6] != null;
        $cond5 = strlen($Line[7]) == 1 and $Line[7] != null;
        $cond6 = preg_match($formatoFecha, $Line[13]) || $Line[13] == null;
        $cond7 = preg_match($formatoFecha, $Line[14]) || $Line[14] == null;
        

        return $cond1 && $cond2 && $cond3 && $cond4 && $cond5 && $cond6 && $cond7;
    }

    public function CorrectLine($line){
        
        if (!($line[4] == "S" || $line[4] == "N")){
            $line[4] = null;
            return true;
        }
        return false;
    }
}

class NotasChecker extends DataCharge{
    public function CheckFormat($Line){
        //0 -Codigo plan no me importa por ahora
        //1 -Plan tmp me importa
        //2 -COHORTE no es null ya que me fijo en estudiantes, pero debe seguir el formato YYYY-XX
        //3 -Sede no me importa
        //4 -Run si me importa
        //5 -Dv si me importa con largo 1
        //6 -Nombres me importa
        //7 -Apellido paterno y materno no me importa
        //9 -Numero de alumno no nulo y con largo 6
        //10- Periodo Asignatura no nulo y con formato YYYY-XX
	    //11 - Codigo Asignatura si me importa, no puede ser nulo
        //12 - Asignatura tmb me importa
        //13 - COnvocatoria si me importa   
        //15 - Si calificacion es (NP, P, EX, A, R, nulo), NOTA ES NULO, else no puede ser nulo
        $formatoFecha = '/^\d{4}-\d{2}$/';
        $cond1 = $Line[2] != null && preg_match($formatoFecha, $Line[2]);
        $cond2 = $Line[4] != null;
        $cond3 = $Line[5] != null && strlen($Line[5]) == 1;
        $cond4 = $Line[6] != null;
        $cond5 = $Line[9] != null;
        $cond6 = $Line[10] != null && preg_match($formatoFecha, $Line[10]);
        $cond7 = $Line[11] != null;
        $cond8 = $Line[12] != null;
        $cond9 = $Line[13] != null;
        $cond10 = (in_array($Line[14], ["NP", "P", "EX", "A", "R",""]) && $Line[15] == null)||($Line[15] != null && !in_array($Line[14], ["NP", "P", "EX", "A", "R",""]));

        return $cond1 && $cond2 && $cond3 && $cond4 && $cond5 && $cond6 && $cond7 && $cond8 && $cond9 && $cond10;
    }

    public function CorrectLine($line){
        $GoodLines = [];


        if (in_array($line[14], ["NP", "P", "EX", "A", "R",""]) && $line[15] != null){
            $line[15] = null;
            $GoodLines[] = $line;
            return true;
        }
        return false;
    }
}

class DocentesChecker extends DataCharge{
    public function CheckFormat($Line){
        
        //0 -RUN NO PUEDE SER NULL
        //6- dedicacion es int
        //8- diurno debe ser diurno
        //9- vespertino debe ser vespertino
        //12- grado academico en ”LICENCIATURA”, ”MAGISTER” o ”DOCTOR”
        //13- jerarquia ”ASISTENTE”, ”ASOCIADO”, ”INSTRUCTOR” o ”TITULAR” o ”DOCENTE”, o ”REGULAR” 
        //”SIN JERARQUIZAR” o n ”COMISION SUPERIOR”
        $cond1 = $Line[0] != null;
        $cond2 = is_numeric($Line[6]);
        $cond3 = $Line[8] == "DIURNO" || $Line[8] == null;
        $cond4 = $Line[9] == "VESPERTINO" || $Line[9] == null;
        $cond5 = in_array($Line[12], ["LICENCIATURA", "MAGISTER", "DOCTOR"]) || $Line[12] == null;
        $cond6 = in_array($Line[13], ["ASISTENTE", "ASOCIADO", "INSTRUCTOR" , "TITULAR" , "DOCENTE", "REGULAR", "SIN JERARQUIZAR" ,"COMISION SUPERIOR"]) || $Line[13] == null;

        return $cond1 && $cond2 && $cond3 && $cond4 && $cond5 && $cond6;
    }

    public function CorrectLine($Line){
        if (!($Line[8] == "DIURNO" || $Line[8] == null)){
            $Line[8] = "DIURNO";
            return true;
        }

        if (!($Line[9] == "VESPERTINO" || $Line[9] == null)){
            $Line[9] = "VESPERTINO";
            return true;
        }

        return false;
    }
}

class PlaneacionChecker extends DataCharge{
    public function CheckFormat($Line){
        
        // 0 - Periodo no puede ser null y debe estar en formato YYYY-XX
        $cond0 = $Line[0] != null && preg_match('/^\d{4}-\d{2}$/', $Line[0]);

        // 1 - Sede debe estar en ["HOGWARTS", "BEAUXBATON", "UAGADOU"]
        $cond1 = in_array(strtoupper($Line[1]), ["HOGWARTS", "BEAUXBATON", "UAGADOU"]);

        // 5 - Id asignatura no puede ser null
        $cond2 = $Line[5] != null;

        // 7 - Sección debe ser un número entero
        $cond3 = is_numeric($Line[7]) && intval($Line[7]) == $Line[7];

        // 8 - Duración debe ser "S", "A" o "I"
        $cond4 = in_array($Line[8], ["S", "A", "I"]);

        // 9 - Jornada debe ser "Vespertino" o "Diurno"
        $cond5 = $Line[9] == "Vespertino" || $Line[9] == "Diurno";

        // 10 - Cupo debe ser un número entero
        $cond6 = is_numeric($Line[10]) && intval($Line[10]) == $Line[10];

        // 11 - Inscritos debe ser un número entero
        $cond7 = is_numeric($Line[11]) && intval($Line[11]) == $Line[11];

        // 12 - Día debe estar en ["lunes", "martes", "miércoles", "jueves", "viernes", "sábado"]
        $cond8 = in_array($Line[12], ["lunes", "martes", "miércoles", "jueves", "viernes", "sábado"]);

        // 13 - Hora de inicio debe estar en formato HH:MM
        $cond9 = preg_match('/^\d{2}:\d{2}$/', $Line[13]);

        // 14 - Hora de fin debe estar en formato HH:MM
        $cond10 = preg_match('/^\d{2}:\d{2}$/', $Line[14]);

        // 15 - Fecha de inicio debe estar en formato dd/MM/YYYY
        $cond11 = preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/', $Line[15]);

        // 16 - Fecha de fin debe estar en formato dd/MM/YYYY
        $cond12 = preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/', $Line[16]);

        // 20 - RUN no puede ser null
        $cond13 = $Line[20] != null;

        // 21 - Nombre no puede ser null
        $cond14 = $Line[21] != null;


        return $cond0 && $cond1 && $cond2 && $cond3 && $cond4 && $cond5 &&
       $cond6 && $cond7 && $cond8 && $cond9 && $cond10 && $cond11 &&
       $cond12 && $cond13 && $cond14;

    }

    public function CorrectLine($Line){
        if (!$Line[9] == "Vespertino" || $Line[9] == "Diurno"){
            $Line[9] = "Diurno";
            return true;
        }

        return false;
    }
}

class PlanesChecker extends DataCharge{
    public function CheckFormat($Line){
        //Codigo not null
        $cond0 = $Line[0] != null;

        //jornada entre diurno o vespertino
        $cond1 = in_array($Line[4], ["Diurno", "Vespertino"]);

        //sede tiene que estar en 3 opciones
        $cond2 = in_array(strtoupper($Line[5]), ["HOGWARTS", "BEAUXBATON", "UAGADOU"]);

        //grado es pregrado, postgrado o Programa Especial
        $cond3 = in_array(strtoupper($Line[6]), ["PREGRADO", "POSTGRADO", "PROGRAMA ESPECIAL"]);

        //modalidad es online o presencial
        $cond4 = in_array(strtoupper($Line[7]), ["ONLINE", "PRESENCIAL"]);

        //inicio vigencia es dd/mm/yyyy
        $cond5 = preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/', $Line[8]);

        return $cond0 && $cond1 && $cond2 && $cond3 && $cond4 && $cond5;
    }

    public function CorrectLine($line){
        if ($line[0] == null){
            return false;
        }

        $line[4] = $this->encontrarPalabraCercana($line, 4, ["Diurno", "Vespertino"]);
        $line[5] = $this->encontrarPalabraCercana($line, 5, ["Hogwarts", "Beauxbaton", "Uagadou"]);
        $line[6] = $this->encontrarPalabraCercana($line, 6, ["Pregrado", "Postgrado", "Programa Especial"]);
        $line[7] = $this->encontrarPalabraCercana($line, 7, ["OnLine", "Presencial"]);
        return true;
    }

    private function encontrarPalabraCercana($line,$i, $palabrasAceptables) {
        // Convertir la palabra a mayúsculas
        $palabraUpper = $line[$i];
    
        // Inicializar variables para la palabra más cercana y la distancia mínima
        $palabraCercana = null;
        $distanciaMinima = PHP_INT_MAX;
    
        // Iterar sobre las palabras aceptables
        foreach ($palabrasAceptables as $palabraAceptable) {
            // Calcular la distancia de Levenshtein
            $distancia = levenshtein($palabraUpper, $palabraAceptable);
    
            // Si la distancia es menor que la mínima registrada, actualizar
            if ($distancia < $distanciaMinima) {
                $distanciaMinima = $distancia;
                $palabraCercana = $palabraAceptable;
            }
        }
        return $palabraCercana;
    }
}
class PrerequisitosChecker extends DataCharge{
    public function CheckFormat($Line){
        //Asignatura id not null
        $cond0 = $Line[1] != null;

        //nivel es entero
        $cond1 = is_numeric($Line[3]) && intval($Line[3]) == $Line[3];

        //ingreso tiene que ser Ingreso
        if ("INGRESO" == strtoupper($Line[4]) && $Line[4] == "ingreso"){
            $cond2 = false;
        }

        else{
            $cond2 = true;
        }

        echo "cond 1 es {$cond0}\n";
        echo "cond 2 es {$cond1}\n";
        echo "cond 3 es {$cond2}\n";

        return $cond0 && $cond1 && $cond2;
    }

    public function CorrectLine($line){
        if ($line[4] == "ingreso"){
            $line[4] == "Ingreso";
            return true;
        }
        return false;
    }
}
?>