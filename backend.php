<?php

header("Content-Type:text/html;charset=utf-8");
header("Access-Control-Allow-Origin: *");

class MyDB extends SQLite3
{
    public function __construct()
    {
        $this->open('testDB.db');
    }
}

function connect()
{
    $db = new MyDB();
    if (!$db) {
        echo $db->lastErrorMsg();
    } else {
        // echo "Opened database successfully\n";
    }

    return $db;
}

function create()
{
    $db = connect();
    $sql = <<<EOF
      create table if not exists user(mobileHash varchar(80), address char(35));
EOF;

    $db->exec($sql);

    $sql = <<<EOF
      create table if not exists teacher(name varchar(30), grade double, id int);
EOF;
    $db->exec($sql);

    $sql = <<<EOF
        create table if not exists comment(id int primary key not null, grade int);
EOF;
    $db->exec($sql);
    $sql = <<<EOF
        create table if not exists verify(verifyCode int, address char(35));
EOF;
    $db->exec($sql);

    $db->close();

}

// function insertTeacher($name)
// {
//     $db = connect();
//     $flag = true;
//     $sql = <<<EOF
//         select name from teacher where name = '$name';
// EOF;
//     $ret = $db->query($sql);

//     while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
//         if ($row['name'] != null) {
//             $flag = false;

//         } else {
//             $flag = true;
//         }

//     }

//     if ($flag) {
//         $sql = <<<EOF
//                 insert into teacher values('$name', NULL, NULL);
// EOF;
//         $db->exec($sql);
//     }
// }

// function updateTeacher($name, $grade, $idStatus)
// {
//     $db = connect();
//     $sql = <<<EOF
//         select grade from teacher where name = '$name';
// EOF;
//     $ret = $db->query($sql);
//     while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
//         $grade += $row['grade'];
//     }

//     $sql = <<<EOF
//         update teacher set grade = $grade where name = '$name';
// EOF;
//     $db->exec($sql);
// }

function updateComment($id, $status)
{
    $db = connect();
    $sql = <<<EOF
        select id from comment where id = $id;
EOF;
    $ret = $db->query($sql);
    $row = $ret->fetchArray(SQLITE3_ASSOC);
    if (!$row) {
        $sql = <<<EOF
            insert into comment values($id, $status);
EOF;
        $db->exec($sql);
    } else {
        $sql = <<<EOF
        select grade from comment where id = $id;
EOF;
        $ret = $db->query($sql);
        $grade = $ret->fetchArray()['grade'];
        $grade += $status;
        $sql = <<<EOF
            update comment set grade = $grade where id = $id;
EOF;
        $db->exec($sql);
    }
    $db->close();
}

function verify($address)
{
    $db = connect();
    $sql = <<<EOF
        select address from user;
EOF;
    $ret = $db->query($sql);

    while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
        if ($address != $row['address']) {
            $db->close();
            return false;
        }

    }
    $db->close();
    return true;
}

function register($userNumber, $mobile, $address)
{

    $db = connect();
    $mobileHash = password_hash($mobile, PASSWORD_DEFAULT);
    $flag = true;
    $sql = <<<EOF
        select verifyCode from verify where address = $address;
EOF;
    $ret = $db->query($sql);
    $textNumber = $ret->fetchArray();
    echo $textNumber;

    if ($textNumber == $userNumber) {
        $sql = <<<EOF
            select mobileHash from user;
EOF;
        $ret = $db->query($sql);

        while ($row = $ret->fetchArray(SQLITE3_ASSOC)) {
            if (password_verify($mobile, $row['mobileHash'])) {
                $flag = false;
            }

        }
        if ($flag) {
            $sql = <<<EOF
                insert into user values('$mobileHash', '$address');
EOF;
            $db->exec($sql);
        }
    } else {
        $flag = false;
    }
    $db->close();
    return $flag;
}

function message($mobile, $address)
{
    $apikey = "bb4b0d6e8fe9a3d6c79ec6f264e4f70f"; //修改为您的apikey(https://www.yunpian.com)登录官网后获取
    $number = rand(1000, 10000);
    $text = "您的验证码是" . $number;
    $ch = curl_init();

    echo $text;
    $data = array('text' => $text, 'apikey' => $apikey, 'mobile' => $mobile);
    $json_data = send($ch, $data);
    $array = json_decode($json_data, true);

    $db = connect();
    $sql = <<<EOF
        insert into verify values($number, $address);
EOF;
    $db->exec($sql);

}

function send($ch, $data)
{
    curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $result = curl_exec($ch);
    $error = curl_error($ch);
    checkErr($result, $error);
    return $result;
}

function checkErr($result, $error)
{
    if ($result === false) {
        echo 'Curl error: ' . $error;
    } else {
        //echo '操作完成没有任何错误';
    }
}; // function
