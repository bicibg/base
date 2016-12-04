<?php
/**
 * Created by PhpStorm.
 * User: bugra
 * Date: 27.11.2016
 * Time: 13:16
 */

namespace User;

use Framework\Database\AbstractDatabase as DB;
use Framework\Database\AbstractDatabaseObject;

class User extends AbstractDatabaseObject
{
    static protected $_TABLE = "user";
    static protected $_KEY = "id";

    public function __construct($id)
    {
        parent::__construct($id);
    }

    public static function register($uname,$email,$upass,$code)
    {
        try
        {
            $password = md5($upass);
            DB::execute("INSERT INTO user(name,email,pass,tokenCode) VALUES(?,?,?,?)",[$uname,$email,$password,$code]);
            $id = DB::lastInsert();
            return new User($id);
        }catch(\PDOException $ex)
        {
            echo $ex->getMessage();
        }
    }

    public static function login($email,$upass)
    {
        try
        {
            session_start();

            $userRow = DB::query("SELECT * FROM user WHERE email=?",[$email]);

            if(count($userRow) == 1)
            {
                if($userRow['status']=="Y")
                {
                    if($userRow['pass']==md5($upass))
                    {
                        $_SESSION['userSession'] = $userRow['id'];
                        return true;
                    }
                    else
                    {
                        header("Location: index.php?error");
                        exit;
                    }
                }
                else
                {
                    header("Location: index.php?inactive");
                    exit;
                }
            }
            else
            {
                header("Location: index.php?error");
                exit;
            }
        }
        catch(\PDOException $ex)
        {
            echo $ex->getMessage();
        }
    }

    public static function is_logged_in()
    {
        if(isset($_SESSION['userSession']))
        {
            return true;
        }
    }

    public function logout()
    {
        session_destroy();
        $_SESSION['userSession'] = false;
    }

    public function verify(){
        $this->setStatus("Y");
    }

    public static function getByEmail($email)
    {
        $query = "SELECT * FROM user WHERE email = ?";
        $res = DB::one($query,[$email]);
        if(count($res)){
            return new User($res["id"]);
        }
        return false;
    }
}