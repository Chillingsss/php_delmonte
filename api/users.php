<?php
include "headers.php";


class User
{
  function login($json)
  {
    // {"username":"admin","password":"admin"}
    include "connection.php";
    $json = json_decode($json, true);
    $sql = "SELECT * FROM tbl_user WHERE user_username = :username  AND BINARY user_password = :password";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $json['username']);
    $stmt->bindParam(':password', $json['password']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetch(PDO::FETCH_ASSOC)) : 0;
  }

  function signup($json)
  {
    include "connection.php";
    $conn->beginTransaction();
    try {
      $json = json_decode($json, true);
      $personalInformation = $json['personalInformation'];
      $educationalBackground = $json['educationalBackground'];
      $employmentHistory = $json['employmentHistory'];
      $positionApplied = $json['positionApplied'];
      if (recordExists($personalInformation['email'], "tbl_personal_information", "email")) {
        // email already exist
        return -1;
      }
      $sql = "INSERT INTO tbl_personal_information (
              last_name, first_name, middle_name, contact_number,
              alternate_contact_number, email, alternate_email,
              present_address, permanent_address, date_of_birth, sex, sss_number,
              tin_number, philhealth_number, pagibig_number, personal_password)
              VALUES (:last_name, :first_name, :middle_name, :contact_number,
              :alternate_contact_number, :email, :alternate_email,
              :present_address, :permanent_address, :date_of_birth, :sex, :sss_number,
              :tin_number, :philhealth_number, :pagibig_number, :personal_password)";
      $stmt = $conn->prepare($sql);

      $stmt->bindParam(':last_name', $personalInformation['last_name']);
      $stmt->bindParam(':first_name', $personalInformation['first_name']);
      $stmt->bindParam(':middle_name', $personalInformation['middle_name']);
      $stmt->bindParam(':contact_number', $personalInformation['contact_number']);
      $stmt->bindParam(':alternate_contact_number', $personalInformation['alternate_contact_number']);
      $stmt->bindParam(':email', $personalInformation['email']);
      $stmt->bindParam(':alternate_email', $personalInformation['alternate_email']);
      $stmt->bindParam(':present_address', $personalInformation['present_address']);
      $stmt->bindParam(':permanent_address', $personalInformation['permanent_address']);
      $stmt->bindParam(':date_of_birth', $personalInformation['date_of_birth']);
      $stmt->bindParam(':sex', $personalInformation['sex']);
      $stmt->bindParam(':sss_number', $personalInformation['sss_number']);
      $stmt->bindParam(':tin_number', $personalInformation['tin_number']);
      $stmt->bindParam(':philhealth_number', $personalInformation['philhealth_number']);
      $stmt->bindParam(':pagibig_number', $personalInformation['pagibig_number']);
      $stmt->bindParam(':personal_password', $personalInformation['personal_password']);
      $stmt->execute();
      $newId = $conn->lastInsertId();
      if ($stmt->rowCount() > 0) {
        $sql = "INSERT INTO tbl_educational_background (
                  personal_info_id, courses_id, school_id, date_of_graduation, prc_license_number)
                  VALUES (:personal_info_id, :courses_id, :school_id, :date_of_graduation, :prc_license_number)";
        foreach ($educationalBackground as $item) {
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':personal_info_id', $newId);
          $stmt->bindParam(':courses_id', $item['courses_id']);
          $stmt->bindParam(':school_id', $item['school_id']);
          $stmt->bindParam(':date_of_graduation', $item['date_of_graduation']);
          $stmt->bindParam(':prc_license_number', $item['prc_license_number']);
          $stmt->execute();
        }
        if ($stmt->rowCount() > 0) {
          $sql = "INSERT INTO tbl_employment_history (
                    personal_info_id, employment_position_name, employment_company_name, employment_start_date, employment_end_date)
                    VALUES (:personal_info_id, :employment_position_name, :employment_company_name, :employment_start_date, :employment_end_date)";
          foreach ($employmentHistory as $item) {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':personal_info_id', $newId);
            $stmt->bindParam(':employment_position_name', $item['employment_position_name']);
            $stmt->bindParam(':employment_company_name', $item['employment_company_name']);
            $stmt->bindParam(':employment_start_date', $item['employment_start_date']);
            $stmt->bindParam(':employment_end_date', $item['employment_end_date']);
            $stmt->execute();
          }
          if ($stmt->rowCount() > 0) {
            $sql = "INSERT INTO tbl_position_applied (
                      personal_info_id, apply_position_id)
                      VALUES (:personal_info_id, :apply_position_id)";
            foreach ($positionApplied as $item) {
              $stmt = $conn->prepare($sql);
              $stmt->bindParam(':personal_info_id', $newId);
              $stmt->bindParam(':apply_position_id', $item['apply_position_id']);
              $stmt->execute();
              if ($stmt->rowCount() > 0) {
                $sql = "INSERT INTO tbl_consent(personal_info_id, subscribe_to_email_updates)
                          VALUES (:personal_info_id, :subscribe_to_email_updates)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':personal_info_id', $newId);
                $stmt->bindParam(':subscribe_to_email_updates', $json['subscribe_to_email_updates']);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                  $conn->commit();
                  return 1;
                }
              }
            }
          }
        }
      }
    } catch (PDOException $th) {
      $conn->rollBack();
      return $th;
    }
  }

  function getInstitution()
  {
    include "connection.php";
    $sql = "SELECT * FROM tbl_institution";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
  }

  function getCourses()
  {
    include "connection.php";
    $sql = "SELECT * FROM tbl_courses";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
  }

  function sendEmail($json)
  {
    include "send_email.php";
    // {"emailToSent":"xhifumine@gmail.com","emailSubject":"Kunwari MESSAGE","emailBody":"Kunwari message ni diri hehe <b>102345</b>"}
    $data = json_decode($json, true);
    $sendEmail = new SendEmail();
    return $sendEmail->sendEmail($data['emailToSent'], $data['emailSubject'], $data['emailBody']);
  }

  function getActiveJob()
  {
    include "connection.php";
    $sql = "SELECT a.*, COUNT(b.apply_position_id) as Total_Applied
              FROM tbl_apply_position a
              LEFT JOIN tbl_position_applied b
              ON a.apply_position_id = b.apply_position_id
              WHERE a.apply_position_status = 1
              GROUP BY a.apply_position_id ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
  }



} //user

function recordExists($value, $table, $column)
{
  include "connection.php";
  $sql = "SELECT COUNT(*) FROM $table WHERE $column = :value";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":value", $value);
  $stmt->execute();
  $count = $stmt->fetchColumn();
  return $count > 0;
}

function uploadImage()
{
  if (isset($_FILES["file"])) {
    $file = $_FILES['file'];
    // print_r($file);
    $fileName = $_FILES['file']['name'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileSize = $_FILES['file']['size'];
    $fileError = $_FILES['file']['error'];
    // $fileType = $_FILES['file']['type'];

    $fileExt = explode(".", $fileName);
    $fileActualExt = strtolower(end($fileExt));

    $allowed = ["jpg", "jpeg", "png"];

    if (in_array($fileActualExt, $allowed)) {
      if ($fileError === 0) {
        if ($fileSize < 25000000) {
          $fileNameNew = uniqid("", true) . "." . $fileActualExt;
          $fileDestination = 'images/' . $fileNameNew;
          move_uploaded_file($fileTmpName, $fileDestination);
          return $fileNameNew;
        } else {
          return 4;
        }
      } else {
        return 3;
      }
    } else {
      return 2;
    }
  } else {
    return "";
  }
}

function getCurrentDate()
{
  $today = new DateTime("now", new DateTimeZone('Asia/Manila'));
  return $today->format('Y-m-d h:i:s A');
}

$input = json_decode(file_get_contents('php://input'), true);

$operation = isset($input["operation"]) ? $input["operation"] : "0";
$json = isset($input["json"]) ? $input["json"] : "0";

$user = new User();

switch ($operation) {
  case "login":
    echo $user->login($json);
    break;
  case "signup":
    echo $user->signup($json);
    break;
  case "getInstitution":
    echo $user->getInstitution();
    break;
  case "getCourses":
    echo $user->getCourses();
    break;
  case "sendEmail":
    echo $user->sendEmail($json);
    break;
  case "getActiveJob":
    echo $user->getActiveJob();
    break;
  default:
    echo json_encode("WALA KA NAGBUTANG OG OPERATION SA UBOS HAHAHHA BOBO");
    http_response_code(400); // Bad Request
    break;
}

