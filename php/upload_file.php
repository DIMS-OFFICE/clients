<?php
    $dir=realpath(dirname(__FILE__)."/..");

    require($dir."/php/pdo_db_connect.php");

    if (isset($_FILES['uploadImage']) || isset($_POST['btnSubmit'])) {
        $s=$db->prepare("SELECT user_id FROM service_users_sessions WHERE hash=:hash");
        $s->bindValue(":hash", $_POST["hash"]);

        $s->execute();

        if ($s->rowCount()==0) {
            $res=Array(
                "status" => "error",
                "desc" => "SESSION_ERROR"
            );

            echo json_encode($res);

            exit();
        }

        $res=Array(
            "status" => "ERROR"
        );

        $parts = explode(".", $_FILES["uploadImage"]["name"]);

        $ext=$parts[count($parts)-1];

        $folderPath=$dir."/temp/";

        if ($_POST["action"]=="1s_report") {

            $file=$dir."/temp/1s_report.xlsx";
        } else if ($_POST["action"]=="change_call_period") {

            $file=$dir."/temp/change_call_period.csv";
        }

        if (! is_writable($folderPath) || ! is_dir($folderPath)) {
            $res=Array(
                "status" => "error",
                "desc" => "Директория недоступна для записи"
            );

            echo json_encode($res);

            exit();
        }

        if (move_uploaded_file($_FILES["uploadImage"]["tmp_name"], $file)) {

        }

        if ($_POST["action"]=="1s_report") {
            exec("xlsx2csv ".$file." --delimiter ';' > ".$dir."/temp/1s_report.csv");

            $csv=file_get_contents($dir."/temp/1s_report.csv");

            $res=Array(
                "status" => "OK"
            );
        } else if ($_POST["action"]=="change_call_period") {
            $res=Array(
                "status" => "OK"
            );
        }
    }

    echo json_encode($res);    
?>