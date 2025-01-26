<?php

/******************************************************************************
 * Пример использования:
 *
 * 1) Подключаетесь к базе:
 *    $connect = mysqli_connect("localhost", "root", "", "connecthub");
 *
 * 2) Вызываете нужную функцию, передавая ресурс подключения.
 *    Например:
 *    getPoles($connect);
 *    getPole($connect, 1);
 *    addPole($connect, $_POST); // и т.д.
 *
 ******************************************************************************/

/*==============================================================================
  ФУНКЦИИ ДЛЯ ТАБЛИЦЫ `poles`
 ==============================================================================*/

/**
 * Получить все записи из таблицы `poles`
 */
$connect = mysqli_connect("localhost", "root", "", "connecthub");

function getPoles($connect)
{
    $query = mysqli_query($connect, "SELECT * FROM `poles`");
    $result = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $result[] = $row;
    }

    echo json_encode($result);
}

/**
 * Получить одну запись из таблицы `poles` по ID
 */
function getPole($connect, $id)
{
    $query = mysqli_query($connect, "SELECT * FROM `poles` WHERE `id` = $id");

    if (mysqli_num_rows($query) === 0) {
        http_response_code(404);
        echo json_encode([
            "status"  => false,
            "message" => "Pole not found"
        ]);
    } else {
        $pole = mysqli_fetch_assoc($query);
        echo json_encode($pole);
    }
}

/**
 * Добавить новую запись в таблицу `poles`
 * Ожидается, что в $data будут ключи:
 *   $data['location']
 *   $data['physical_cond']
 *   $data['max_connection']
 *   $data['current_connection']
 */
function addPole($connect, $data)
{
    $location           = $data['location'];
    $physical_cond      = $data['physical_cond'];
    $max_connection     = (int) $data['max_connection'];
    $current_connection = (int) $data['current_connection'];

    $sql = "INSERT INTO `poles` (`location`, `physical_cond`, `max_connection`, `current_connection`) 
            VALUES ('$location', '$physical_cond', $max_connection, $current_connection)";

    mysqli_query($connect, $sql);

    http_response_code(201);
    echo json_encode([
        "status" => true,
        "pole_id" => mysqli_insert_id($connect)
    ]);
}

/**
 * Обновить запись в таблице `poles` по ID
 * Ожидается, что в $data будут ключи:
 *   $data['location']
 *   $data['physical_cond']
 *   $data['max_connection']
 *   $data['current_connection']
 */
function updatePole($connect, $id, $data)
{
    $location           = $data['location'];
    $physical_cond      = $data['physical_cond'];
    $max_connection     = (int) $data['max_connection'];
    $current_connection = (int) $data['current_connection'];

    $sql = "UPDATE `poles`
            SET `location` = '$location',
                `physical_cond` = '$physical_cond',
                `max_connection` = $max_connection,
                `current_connection` = $current_connection
            WHERE `id` = $id";

    mysqli_query($connect, $sql);

    http_response_code(200);
    echo json_encode([
        "status"  => true,
        "message" => "Pole is updated"
    ]);
}

/**
 * Удалить запись из таблицы `poles` по ID
 */
function deletePole($connect, $id)
{
    mysqli_query($connect, "DELETE FROM `poles` WHERE `id` = $id");

    http_response_code(200);
    echo json_encode([
        "status"  => true,
        "message" => "Pole is deleted"
    ]);
}

/*==============================================================================
  ФУНКЦИИ ДЛЯ ТАБЛИЦЫ `connection_requests`
 ==============================================================================*/

/**
 * Получить все записи из таблицы `connection_requests`
 */
function getConnectionRequests($connect)
{
    $query = mysqli_query($connect, "SELECT * FROM `connection_requests`");
    $result = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $result[] = $row;
    }

    echo json_encode($result);
}

/**
 * Получить одну запись из таблицы `connection_requests` по ID
 */
function getConnectionRequest($connect, $id)
{
    $query = mysqli_query($connect, "SELECT * FROM `connection_requests` WHERE `id` = $id");

    if (mysqli_num_rows($query) === 0) {
        http_response_code(404);
        echo json_encode([
            "status"  => false,
            "message" => "Connection Request not found"
        ]);
    } else {
        $row = mysqli_fetch_assoc($query);
        echo json_encode($row);
    }
}

/**
 * Добавить новую запись в таблицу `connection_requests`
 * Ожидается, что в $data будут ключи:
 *   $data['poles_id']
 *   $data['users_id']
 *   $data['status']
 */
function addConnectionRequest($connect, $data)
{
    $poles_id = (int) $data['poles_id'];
    $users_id = (int) $data['users_id'];
    $status   = (int) $data['status'];

    $sql = "INSERT INTO `connection_requests` (`poles_id`, `users_id`, `status`)
            VALUES ($poles_id, $users_id, $status)";

    mysqli_query($connect, $sql);

    http_response_code(201);
    echo json_encode([
        "status"               => true,
        "connection_request_id"=> mysqli_insert_id($connect)
    ]);
}

/**
 * Обновить запись в таблице `connection_requests` по ID
 * Ожидается, что в $data будут ключи:
 *   $data['poles_id']
 *   $data['users_id']
 *   $data['status']
 */
function updateConnectionRequest($connect, $id, $data)
{
    $poles_id = (int) $data['poles_id'];
    $users_id = (int) $data['users_id'];
    $status   = (int) $data['status'];

    $sql = "UPDATE `connection_requests`
            SET `poles_id` = $poles_id,
                `users_id` = $users_id,
                `status`   = $status
            WHERE `id` = $id";

    mysqli_query($connect, $sql);

    http_response_code(200);
    echo json_encode([
        "status"  => true,
        "message" => "Connection Request is updated"
    ]);
}

/**
 * Удалить запись из таблицы `connection_requests` по ID
 */
function deleteConnectionRequest($connect, $id)
{
    mysqli_query($connect, "DELETE FROM `connection_requests` WHERE `id` = $id");

    http_response_code(200);
    echo json_encode([
        "status"  => true,
        "message" => "Connection Request is deleted"
    ]);
}

/*==============================================================================
  ФУНКЦИИ ДЛЯ ТАБЛИЦЫ `pole_install_requests`
 ==============================================================================*/

/**
 * Получить все записи из таблицы `pole_install_requests`
 */
function getPoleInstallRequests($connect)
{
    $query = mysqli_query($connect, "SELECT * FROM `pole_install_requests`");
    $result = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $result[] = $row;
    }

    echo json_encode($result);
}

/**
 * Получить одну запись из таблицы `pole_install_requests` по ID
 */
function getPoleInstallRequest($connect, $id)
{
    $query = mysqli_query($connect, "SELECT * FROM `pole_install_requests` WHERE `id` = $id");

    if (mysqli_num_rows($query) === 0) {
        http_response_code(404);
        echo json_encode([
            "status"  => false,
            "message" => "Pole Install Request not found"
        ]);
    } else {
        $row = mysqli_fetch_assoc($query);
        echo json_encode($row);
    }
}

/**
 * Добавить новую запись в таблицу `pole_install_requests`
 * Ожидается, что в $data будут ключи:
 *   $data['location']
 *   $data['users_id']
 *   $data['admin_comment']
 *   $data['status']
 */
function addPoleInstallRequest($connect, $data)
{
    $location      = $data['location'];
    $users_id      = (int) $data['users_id'];
    $admin_comment = $data['admin_comment'];
    $status        = (int) $data['status'];

    $sql = "INSERT INTO `pole_install_requests` (`location`, `users_id`, `admin_comment`, `status`)
            VALUES ('$location', $users_id, '$admin_comment', $status)";

    mysqli_query($connect, $sql);

    http_response_code(201);
    echo json_encode([
        "status"                  => true,
        "pole_install_request_id" => mysqli_insert_id($connect)
    ]);
}

/**
 * Обновить запись в таблице `pole_install_requests` по ID
 * Ожидается, что в $data будут ключи:
 *   $data['location']
 *   $data['users_id']
 *   $data['admin_comment']
 *   $data['status']
 */
function updatePoleInstallRequest($connect, $id, $data)
{
    $location      = $data['location'];
    $users_id      = (int) $data['users_id'];
    $admin_comment = $data['admin_comment'];
    $status        = (int) $data['status'];

    $sql = "UPDATE `pole_install_requests`
            SET `location`      = '$location',
                `users_id`      = $users_id,
                `admin_comment` = '$admin_comment',
                `status`        = $status
            WHERE `id` = $id";

    mysqli_query($connect, $sql);

    http_response_code(200);
    echo json_encode([
        "status"  => true,
        "message" => "Pole Install Request is updated"
    ]);
}

/**
 * Удалить запись из таблицы `pole_install_requests` по ID
 */
function deletePoleInstallRequest($connect, $id)
{
    mysqli_query($connect, "DELETE FROM `pole_install_requests` WHERE `id` = $id");

    http_response_code(200);
    echo json_encode([
        "status"  => true,
        "message" => "Pole Install Request is deleted"
    ]);
}

/*==============================================================================
  ФУНКЦИИ ДЛЯ ТАБЛИЦЫ `users`
 ==============================================================================*/

/**
 * Получить все записи из таблицы `users`
 */
function getUsers($connect)
{
    $query = mysqli_query($connect, "SELECT * FROM `users`");
    $result = [];

    while ($row = mysqli_fetch_assoc($query)) {
        $result[] = $row;
    }

    echo json_encode($result);
}

/**
 * Получить одну запись из таблицы `users` по ID
 */
function getUser($connect, $id)
{
    $query = mysqli_query($connect, "SELECT * FROM `users` WHERE `id` = $id");

    if (mysqli_num_rows($query) === 0) {
        http_response_code(404);
        echo json_encode([
            "status"  => false,
            "message" => "User not found"
        ]);
    } else {
        $row = mysqli_fetch_assoc($query);
        echo json_encode($row);
    }
}

/**
 * Добавить новую запись в таблицу `users`
 * Ожидается, что в $data будут ключи:
 *   $data['user_name']
 *   $data['password_hash']
 *   $data['role']
 *   $data['contact_info']
 */
function addUser($connect, $data)
{
    $user_name     = $data['user_name'];
    $password_hash = $data['password_hash'];
    $role          = (int) $data['role'];
    $contact_info  = $data['contact_info'];

    $sql = "INSERT INTO `users` (`user_name`, `password_hash`, `role`, `contact_info`)
            VALUES ('$user_name', '$password_hash', $role, '$contact_info')";

    mysqli_query($connect, $sql);

    http_response_code(201);
    echo json_encode([
        "status"  => true,
        "user_id" => mysqli_insert_id($connect)
    ]);
}

/**
 * Обновить запись в таблице `users` по ID
 * Ожидается, что в $data будут ключи:
 *   $data['user_name']
 *   $data['password_hash']
 *   $data['role']
 *   $data['contact_info']
 */
function updateUser($connect, $id, $data)
{
    $user_name     = $data['user_name'];
    $password_hash = $data['password_hash'];
    $role          = (int) $data['role'];
    $contact_info  = $data['contact_info'];

    $sql = "UPDATE `users`
            SET `user_name`     = '$user_name',
                `password_hash` = '$password_hash',
                `role`          = $role,
                `contact_info`  = '$contact_info'
            WHERE `id = $id`";

    mysqli_query($connect, $sql);

    http_response_code(200);
    echo json_encode([
        "status"  => true,
        "message" => "User is updated"
    ]);
}

/**
 * Удалить запись из таблицы `users` по ID
 */
function deleteUser($connect, $id)
{
    mysqli_query($connect, "DELETE FROM `users` WHERE `id` = $id");

    http_response_code(200);
    echo json_encode([
        "status"  => true,
        "message" => "User is deleted"
    ]);
}
