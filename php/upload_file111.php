<?php
    $dir=realpath(dirname(__FILE__)."/..");

    require($dir."/php/pdo_db_connect.php");

    if (isset($_FILES['userfile']) || isset($_FILES['uploadImage'])) {
        /*$s=$db->prepare("SELECT user_id FROM service_users_sessions WHERE hash=:hash");
        $s->bindValue(":hash", $_POST["hash"]);

        $s->execute();

        if ($s->rowCount()==0) {
            $res=Array(
                "status" => "error",
                "desc" => "SESSION_ERROR"
            );

            echo json_encode($res);

            exit();
        }*/

        if (! is_writable($dir."/temp/") || ! is_dir($dir."/temp/")) {
            $res=Array(
                "status" => "error",
                "desc" => "Директория недоступна для записи"
            );

            echo json_encode($res);

            exit();
        }

        if (move_uploaded_file($_FILES["userfile"]["tmp_name"], $dir."/temp/change_call_period.csv")) {
            //exec("xlsx2csv ".$dir."/temp/change_call_period.xlsx --delimiter ';' > ".$dir."/temp/change_call_period.csv"); 

            $res=Array(
                "status" => "OK"
            );
        }

        echo json_encode($res); 
    } else {
        $res=Array(
            "status" => "error",
            "desc" => "Неизвестная ошибка"
        );

        echo json_encode($res); 
    }
?>