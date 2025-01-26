<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
//header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Headers: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


require './connect.php';
require './functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$q = $_GET['q'] ?? substr($_SERVER['REQUEST_URI'], 1);
$params = explode('/', $q);
$type = $params[0];
if (isset($params[1])) {
    $id = $params[1];
}


if ($method === "GET") {
    if ($type === 'index') {

        if (isset($id)) {
            getPost($connect, $id);
        } else {
            getPosts($connect);
        }

    }
} elseif ($method === "POST") {
    if ($type === 'index') {
        addPost($connect, $_POST);
    }
} elseif ($method === "PATCH") {
    if ($type === 'index') {
        if (isset($id)) {
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);
            updatePost($connect, $id, $data);
        }
    }
} elseif ($method === 'DELETE') {
    if ($type === 'index') {
        if (isset($id)) {
            deletePost($connect, $id);
        }
    }
}


// Вспомогательная функция для перевода названия организации из кириллицы в латиницу
function transliterate($text)
{
    // Простейшая замена символов. Можно расширять по необходимости.
    $map = [
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh',
        'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
        'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
        'Я' => 'Ya', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h',
        'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e',
        'ю' => 'yu', 'я' => 'ya', ' ' => '_'
    ];
    return strtr($text, $map);
}

// Роутинг
if ($method === 'POST') {

    // ----------------------------------------------------------------------
    // 1. Авторизация: POST /auth
    // ----------------------------------------------------------------------
    if ($type === 'login') {
        $login = $_POST['login'] ?? '';
        $pass = $_POST['password'] ?? '';

        // Пример запроса в таблицу users
        // В таблице users, предположим, есть поля: id, login, password, role, ...
        $sql = "SELECT role FROM users WHERE login = ? AND password = ? LIMIT 1";
        $stmt = $connect->prepare($sql);
        $stmt->execute([$login, $pass]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Авторизация успешна
            echo json_encode([
                'result_code' => 0,
                'role' => $user['role']
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // Авторизация не успешна
            echo json_encode([
                'result_code' => 1
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // ----------------------------------------------------------------------
    // 2. Восстановление пароля: POST /forgot
    // ----------------------------------------------------------------------
    if ($type === 'forgot') {
        $organization = $_POST['organization'] ?? '';
        $email = $_POST['email'] ?? '';

        // Ищем в таблице users запись с совпадающим названием и email
        // Предположим, что в таблице users есть столбцы organization_name, contact_email
        // или вы храните email в каком-то другом поле — адаптируйте под себя
        $sql = "SELECT * FROM users WHERE User_name = ? AND contact_info = ? LIMIT 1";
        $stmt = $connect->prepare($sql);
        $stmt->execute([$organization, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Теоретическая отправка письма (реализуется по желанию)
            // sendRecoveryMail($user['contact_email'], ...);

            // Возвращаем успешный код
            echo json_encode([
                'result' => 0
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // Нет такой организации или email
            echo json_encode([
                'result' => 1
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // ----------------------------------------------------------------------
    // 3. Регистрация: POST /register
    // ----------------------------------------------------------------------
    if ($type === 'register') {
        $organization = $_POST['organization'] ?? '';
        $orgType = $_POST['org_type'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';

        // Генерируем login и password
        // Login — просто название организации (или любая ваша логика)
        $login = $organization;

        // Password — транслитерация + 5 случайных цифр
        $randomDigits = strval(rand(10000, 99999)); // 5 случайных цифр
        $password = index . phptransliterate($organization) . $randomDigits;

        // Определяем role
        // 2 — сетевой провайдер, 3 — магистральный провайдер
        // Проверим корректность org_type
        $role = ($orgType == 2) ? 2 : 3;
        // Или сделайте заранее проверку, если хотите разрешать только 2 и 3

        // Сохранение в БД
        // Предположим, что в таблице `users` у нас есть столбцы:
        // id, login, password, role, organization_name, contact_phone, contact_email, ...
        $sql = "INSERT INTO users 
                (login, password, role, User_name, contact_info, contact_info) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($sql);
        $stmt->execute([
            $login,
            $password,
            $role,
            $organization,
            $phone,
            $email
        ]);

        // Возвращаем новосозданные логин и пароль
        echo json_encode([
            'login' => $login,
            'password' => $password
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

} // конец проверки $method === 'POST'

// Если запрос пришёл с другими методами (GET, PATCH, DELETE) —
// можно вернуть 404 или просто пустой ответ
http_response_code(404);
echo json_encode([
    'message' => 'Not found'
], JSON_UNESCAPED_UNICODE);




