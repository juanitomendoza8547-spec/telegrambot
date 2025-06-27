<?php

$Base_DTS = mysqli_connect("localhost", "root", "", "liam_druk");


function is_premium(){
    global $userId; global $Mi_Id; global $message_id; global $chatId;
    if(true != verifiPremium($userId) AND true != verifiAdmin($userId)  AND true != veritimepremium($userId) AND $userId != $Mi_Id){
      bot('sendMessage', [
                'chat_id' =>$chatId,
    'reply_to_message_id'=>$message_id,
    'parse_mode'=>'HTML',
                'text' =>"<b>Exclusive command for premium</b>",
                'reply_markup'=> json_encode(['inline_keyboard'=>[
                    [['text'=>"Buy",'url'=>"https://t.me/NovaStranger"]]
                    ],'resize_keyboard'=>true])
                ]);       
    die();
            }
        }


#-----------------------------------------------------------------------------#
function is_credits(){
    global $userId; global $Mi_Id; global $message_id; global $chatId;
    if(true != USERCRED($userId) AND $userId != $Mi_Id){
      bot('sendMessage', [
                'chat_id' =>$chatId,
    'reply_to_message_id'=>$message_id,
    'parse_mode'=>'HTML',
                'text' =>"<b>EXCLUSIVE GATE ONLY WITH CREDITS</b>",
                'reply_markup'=> json_encode(['inline_keyboard'=>[
                    [['text'=>"Buy",'url'=>"https://t.me/NovaStranger"]]
                    ],'resize_keyboard'=>true])
                ]);       
    die();
            }
        }
#------------------------------------------------------------------------------#
function is_rscre(){
    global $Base_DTS; global $userId; global $gId;
    $ress = Cedis($userId);
    $cre = $ress['creditos'];
    $restacredi = $cre - 1 ;
    $sql = "UPDATE Creditos set creditos = '$restacredi' WHERE userdid = '$gId'";
    $result21 = mysqli_query($arr, $sql);
}




