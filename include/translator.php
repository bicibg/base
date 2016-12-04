<?php
/**
 * Created by PhpStorm.
 * User: bugra
 * Date: 10.11.2016
 * Time: 21:49
 */
use Configuration\Config;
use Framework\Database\AbstractDatabase as DB;
use Framework\Util\TrObject;

function translate(){
    global $root;

    if(Config::getParam(["tr_translating"],false)){
        generateTrFile($_SESSION["lang"]);
    }
}

function tr($sourceText, $disambiguation = '', $comment = '') {
    global $trTab;

    $disamb_var = "_|_";
    $shastr = sha1($sourceText.$disamb_var.$disambiguation);
    $change_id = "";
    $pre = "";

    $tr_display_ids = Config::getParam(['tr_display_ids'],false);
    $tr_lang = Config::getParam(['tr_lang'], null);
    $tr_lookupsql = Config::getParam(['tr_lookupsql'],false);
    if ($tr_display_ids) {
        $query = "SELECT `tr_change_id`	FROM `tr_table` WHERE `tr_index` = ?";
        $res = DB::one($query,[$shastr]);

        if(count($res)){
            $change_id = $res['tr_change_id'];
            $pre = $res['tr_change_id'].": ";
        }
    }

    if (isset($trTab[$tr_lang][$shastr])) {
        if (empty($trTab[$tr_lang][$shastr])) {
            trWarn("No Translation available for \"$sourceText\" (id: ".sha1($sourceText.$disamb_var.$disambiguation)."; lang: $tr_lang)");
        }

        return new TrObject($pre.$trTab[$tr_lang][$shastr]);
    } elseif (!$tr_lookupsql) {
        echo $pre.$sourceText;
        return new TrObject($pre.$sourceText);
    }

    $query = "SELECT `tr_index`,`tr_change_id` FROM `tr_table` WHERE `tr_index` = ?";
    $res = DB::one($query,[$shastr]);

    if (!count($res)) {

        $query = "INSERT INTO tr_table (tr_index, tr_text, tr_disam, tr_comment) VALUES (?,?,?,?)";

        DB::execute($query,[$shastr,$sourceText,$disambiguation,$comment]);

    } else {
        if ($tr_lookupsql) {
            $query = "SELECT  tr_translation.tr_translation FROM tr_translation WHERE tr_index=? AND tr_language=?";

            $res = DB::one($query,[$shastr,$tr_lang]);

            if (count($res)) {
                return new TrObject($res['tr_translation']);
            }
        }else{
            return new TrObject($pre.$sourceText);
        }
    }

    trWarn("No Translation available for \"$sourceText\"<br>(id: ".sha1($sourceText.$disamb_var.$disambiguation)."; lang: $tr_lang)");

    return new TrObject($pre.$sourceText);
}

function trWarn($warn_string) {
    $tr_warn = Config::getParam(['tr_warn'], false);
    if ($tr_warn) {
        //pinfoz($warn_string);
        print("<p>tr warning:<br />".$warn_string."</p>");
    }
}

function mysqlizeTrs() {
    global $trTab;
    if (!isset($trTab)) {
        return;
    }
    foreach ($trTab as $language => $arr) {
        foreach ($arr as $index => $value) {
            $query = "SELECT `tr_index` FROM `tr_translation` WHERE `tr_index`= ? AND `tr_language`= ?";
            $res = DB::one($query,[$index,$language]);

            if (!count($res)) {
                $query = "INSERT INTO `tr_translation` (`tr_translation`,`tr_index`, `tr_language`) VALUES(?,?,?)";

            } else {
                $query = "UPDATE tr_translation SET `tr_translation` = ? WHERE `tr_index` = ? AND `tr_language` = ?";
            }
            DB::execute($query,[$value,$index,$language]);
        }
    }
}

function generateTrFile($lan) {
    global $root;
    if(!$lan) return;
    $query = "SELECT    tr_index, tr_text, tr_disam, tr_comment, tr_active, tr_change_id FROM tr_table ORDER BY  tr_change_id";

    $file = fopen("$root/include/translations/$lan.php", "w");
    if (!$file) {
        return;
    }
    fwrite($file, "<?php\n/* Generation Date: ".date("d.m.Y")." */\n\$trlang = \"$lan\";\n\n");

    $res = DB::query($query);
    foreach($res as $row){
        $tran = str_replace("\"", "\\\"", $row['tr_text']);
        $tran = str_replace("\$", "\\\$", $tran);
        $query = "SELECT tr_translation.tr_translation FROM	tr_translation WHERE tr_index=? AND	tr_language=?";
        $res = DB::one($query,[$row["tr_index"],$lan]);

        if (count($res)) {
            $tran = str_replace("\"", "\\\"", $row['tr_translation']);
            $tran = str_replace("\$", "\\\$", $tran);
        }
        fwrite($file, "/* active: $row[tr_active]\ncid: $row[tr_change_id]\n");
        fwrite($file, "text: $row[tr_text]");
        if ($row["tr_disam"]) {
            fwrite($file, "\ncontext: $row[tr_disam]");
        }
        if ($row["tr_comment"]) {
            fwrite($file, "\ncomment: $row[tr_comment]");
        }
        fwrite($file, " */\n");
        fwrite($file, "\$trTab[\$trlang][\"$row[tr_index]\"] = \"$tran\";\n");
    }
    fwrite($file, "\nunset(\$trlang);\n?>");
    fclose($file);
}