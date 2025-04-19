<?php

require 'vendor/autoload.php';

use danog\MadelineProto\API;
use danog\MadelineProto\Settings;
use danog\MadelineProto\Settings\AppInfo;

// MadelineProto sozlamalari

$settings = new Settings();
$appInfo = new AppInfo();
$api_id = trim(readline("Iltioms api id ni kiriting: "));
$appInfo->setApiId($api_id); // O'zingizning api_id ni kiriting
$api_hash = trim(readline("Iltimos api hash kiriting: "));
$appInfo->setApiHash($api_hash); // O'zingizning api_hash ni kiriting
$settings->setAppInfo($appInfo);
$madeline = new API('session.madeline', $settings);



// Avtorizatsiya
try {
    if (!$madeline->getSelf()) {
        echo "Telefon raqamingizni kiriting (+998901234567): ";
        $phone = trim(fgets(STDIN));
        $madeline->phoneLogin($phone);
        echo "Telegram'dan kelgan kodni kiriting: ";
        $code = trim(fgets(STDIN));
        $madeline->completePhoneLogin($code);
        echo "Muvaffaqiyatli kirdingiz!\n";
    }
    $madeline->start();
} catch (\Exception $e) {
    echo "Xato: " . $e->getMessage() . "\n";
    exit;
}

// Terminal interfeysi
while (true) {
    echo "\nMenu: \n";
    echo "1.Kanal\n";
    echo "2.Yozishmalar\n";
    echo "3.Xabarlar\n";
    echo "4.Xabar yuborish\n";
    echo "5.Chiqish\n";
    echo "Iltimos menuni raqam ko'rinishida kiriting: ";

    $command = trim(fgets(STDIN));

    if ($command == 5) {
        break;
    } elseif ($command == 1) {
        // Kanallar va guruhlarni ro'yxatlash
        $dialogs = $madeline->messages->getDialogs();
        echo "Kanallar va guruhlar:\n\n";
        foreach ($dialogs['chats'] as $chat) {
            if (isset($chat['title'])) {
                echo "- {$chat['title']}  Foydalanuvchi Idsi (ID: {$chat['id']})\n";
            }
        }
    } elseif ($command == 2) {
        // Shaxsiy yozishmalarni ro'yxatlash
        $dialogs = $madeline->messages->getDialogs();
        echo "Yozishmalar:\n";
        foreach ($dialogs['users'] as $user) {
            $name = $user['first_name'] . (isset($user['last_name']) ? ' ' . $user['last_name'] : '');
            echo "- $name (ID: {$user['id']})\n";
        }
    } elseif ($command == 3) {
        // Muayyan chat xabarlarini ko'rish
        echo "Xabarlarni ko'rmoqchi bo'lgan chat ID yoki username (@username yoki ID): ";
        $peer = trim(fgets(STDIN));
        try {
            $messages = $madeline->messages->getHistory(['peer' => $peer, 'limit' => 10]);
            echo "Oxirgi xabarlar:\n";
            foreach ($messages['messages'] as $msg) {
                if (isset($msg['message'])) {
                    $sender = isset($msg['from_id']['user_id']) ? $msg['from_id']['user_id'] : 'Noma\'lum';
                    echo "- [$sender]: {$msg['message']} (ID: {$msg['id']})\n";
                }
            }
        } catch (\Exception $e) {
            echo "Xato: " . $e->getMessage() . "\n";
        }
    } elseif ($command == 4) {
        // Xabar yuborish
        echo "Xabar yubormoqchi bo'lgan chat ID yoki username (@username yoki ID): ";
        $peer = trim(fgets(STDIN));
        echo "Xabar matnini kiriting: ";
        $message = trim(fgets(STDIN));
        try {
            $madeline->messages->sendMessage([
                'peer' => $peer,
                'message' => $message
            ]);
            echo "Xabar yuborildi!\n";
        } catch (\Exception $e) {
            echo "Xato: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Noma'lum buyruq. Iltimos, Quydagi menudan birini raqam ko'rinishida kiriting";
    }
}

// Sessiyani yopish
$madeline->stop();

?>