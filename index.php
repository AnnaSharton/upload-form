<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">    
    <title>Upload file</title>
    <style>
        .file-not-upload {padding: 10px; border: 1px solid gray; background: rgb(241, 176, 176);}
        .file-added {padding: 10px; border: 1px solid gray; background: rgb(129, 233, 143);}
    </style>
</head>
<body>
<?php 
    error_reporting(E_ALL);
    mb_internal_encoding("UTF-8");
/////////    функции   ////////////
function generate_string() { //создаю функцию со счетчиком чтобы сгенерировать $count случайных букв
    if(isset($_POST['num'])){
        $count=$_POST['num']; 
        $alphabet=range('a', 'z'); 
        $random_character = '';      
            for($i = 0; $i < $count; $i++) { 
            $random_character .= $alphabet[mt_rand(0, $count - 1)]; //выбираю случайно буквы с индексом от 0 до count-1
            } 
    } return $random_character;
} 
function getname() { //функция для названия файла вместо switch
    if(isset($_POST['fileName']) and is_uploaded_file($_FILES['file']['tmp_name'])) {  
        if ($_POST['fileName']==='date') {return date("Y-m-d");}
        else if ($_POST['fileName']==='date-hours')  {return date("Y-m-d").'__'.date("H-m");}
        else {return generate_string();}
    }
}

function getextension() { //для получения расширения
    if (isset($_FILES['file']['name'])){
            $extension  = '.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            return $extension;
    }
}
$newFileName=getname().getextension();

////// массив сообщений //////
$messages=array(
    0=>"Слишком большой файл",
    1=>"Вы не выбрали файл!",
    2=>'<br><div class="file-added">Файл успешно загружен:'.$newFileName.'  <a href="download.php?file="'.$newFileName.'>Скачать</a></div>',
    3=>"Ошибка загрузки"
);  

//////////     GET вывод названия загруженного файла    //////////

if (isset($_GET['message'])){ //если вызван get запрос вывожу сбщ вначале страницы:
    session_start();
    $a=$_SESSION['data']; //присваиваю переменной $newFileName
    
    echo '<br><div class="file-added">Файл успешно загружен:'.$a.'  <a href="index.php?file='.$a.'">Скачать</a></div>';
}  

////////////  скачивание файла   /////////////////

    if (!empty($_GET['file'])) { 
        $file = basename(($_GET['file']));//имя скачиваемого файла
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Content-Disposition: attachment; filename=".$file);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            while (ob_get_level()) { //про функции вывода не изучала еще но эта штука помогла чтобы не скачивались нечитаемые файлы
                ob_end_clean();
            }
            readfile($file);
            exit; 
    } else if (!empty($_GET['file'])) { //не знаю правильно ли так задавать условие чтобы сбщ ниже не высвечивалось заранее при загрузке страницы
        echo '<div class="file-not-upload">Ошибка скачивания файла</div>';
    }

//////////////////    условия    ////////////////////////

//0 - загруженный файл слишком большой
if (isset($_POST['submit']) and $_FILES['file']['error'] === UPLOAD_ERR_FORM_SIZE) { 
    echo '<br><div class="file-not-upload">'.$messages[0].'</div>';}

//1 -  юзер не выбрал файл
else if(isset($_POST['submit']) and !is_uploaded_file($_FILES['file']['tmp_name']))  { //если нажата кнопка "сохранить" но не выбран файл
    echo '<br><div class="file-not-upload">'.$messages[1].'</div>';
} 

//2 - все ок
else if (isset($_POST['submit']) and is_uploaded_file($_FILES['file']['tmp_name'])) { //преверяю загружен ли файл и нажата submit
    move_uploaded_file($_FILES['file']['tmp_name'], $newFileName); //то указываю через запятую куда переместить файл (в корень проекта) с новым именем
    session_start();   //я про сессии ничего еще не понимаю, но примерно поняла что вот так можно
    $_SESSION['data'] = $newFileName; 
    header('Location: index.php?message=ok'); //редирект
} 

//3 - если нажата submit но что-то пошло не так
else if (isset($_POST['submit'])) {echo '<br><div class="file-not-upload">'.$messages[3].'</div>';} //опять не знаю правильно ли так было задать else if чтобы по умолчанию не высвечивалась ошибка или есть другой способ для этого   
else {}
                     
?>
    <div class="container">
        <div class="form">
            <h2>Загрузить файл</h2>

            <form method="post" action="" enctype="multipart/form-data"> 
                <input type="hidden" name="MAX_FILE_SIZE" value="30000000">
                <input type="file" name="file">
                <h2>Имя файла:</h2>
                <p><input name="fileName" type="radio" id="rb1" value="date">
                <label for="rb1">  текущая дата в формате ГГГГ-ММ-ДД </label></p> 
                <p><input name="fileName" type="radio" id="rb2" value="date-hours">
                <label for="rb2">  текущая дата в формате ГГГГ-ММ-ДД__ЧЧ-ММ</label></p> 
                <p><input name="fileName" type="radio" id="rb3" value="string" checked> 
                <label for="rb3">  случайная строка длиной <input type="number" name="num" min="1" max="100" value="5"> символов</label></p> 
                <input type="submit" id="submit" name="submit" value="Сохранить">
            </form>

        </div>
    </div> 
</body>
</html>