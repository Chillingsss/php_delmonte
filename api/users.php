<?php
include "headers.php";


class User
{
  // function login($json)
  // {
  //   // {"username":"admin","password":"admin"}
  //   include "connection.php";
  //   $json = json_decode($json, true);
  //   $sql = "SELECT * FROM tbl_personal_information WHERE email = :username  AND BINARY personal_password = :password";
  //   $stmt = $conn->prepare($sql);
  //   $stmt->bindParam(':username', $json['username']);
  //   $stmt->bindParam(':password', $json['password']);
  //   $stmt->execute();
  //   return $stmt->rowCount() > 0 ? json_encode($stmt->fetch(PDO::FETCH_ASSOC)) : 0;
  // }

  function login($json)
{
    include "connection.php";
    $json = json_decode($json, true);


    $sql = "SELECT * FROM tbladmin WHERE adm_email = :username AND BINARY adm_password = :password";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $json['username']);
    $stmt->bindParam(':password', $json['password']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode([
            'adm_id' => $user['adm_id'],
            // 'user_level_id' => $user['user_level_id'],
            'adm_user_level' => $user['adm_user_level'],
            'adm_name' => $user['adm_name'],
            'adm_email' => $user['adm_email']
        ]);
    }


    $sql = "SELECT * FROM tblsupervisor WHERE sup_email = :username AND BINARY sup_password = :password";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $json['username']);
    $stmt->bindParam(':password', $json['password']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode([
            'sup_id' => $user['sup_id'],
            'sup_user_level' => $user['sup_user_level'],
            'sup_name' => $user['sup_name'],
            'sup_email' => $user['sup_email']
        ]);
    }


    $sql = "SELECT * FROM tblcandidates WHERE cand_email = :username AND BINARY cand_password = :password";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $json['username']);
    $stmt->bindParam(':password', $json['password']);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode([
            'cand_id' => $user['cand_id'],
            'cand_firstname' => $user['cand_firstname'],
            'cand_lastname' => $user['cand_lastname'],
            'cand_email' => $user['cand_email'],
            'user_level_id' => 'applicant'
        ]);
    }

    return json_encode(null);
}

function signup($json)
{
  include "connection.php";
  $conn->beginTransaction();
  try {
    $json = json_decode($json, true);
    $personalInformation = $json['personalInfo'];
    $educationalBackground = $json['educationalBackground'] ?? [];
    $employmentHistory = $json['employmentHistory'] ?? [];
    $skills = $json['skills'] ?? [];
    $trainings = $json['trainings'] ?? [];
    $knowledge = $json['knowledge'] ?? [];
    $licenses = $json['licenses'] ?? [];
    $createdDateTime = getCurrentDate();
    $isSubscribeToEmail = $json['isSubscribeToEmail'] ?? 0;
    // return json_encode($json);
    // die();
    $sql = "INSERT INTO tblcandidates(cand_lastname, cand_firstname, cand_middlename, cand_contactNo,
              cand_alternateContactNo, cand_email, cand_alternateEmail, cand_presentAddress,
              cand_permanentAddress, cand_dateofBirth, cand_sex, cand_sssNo, cand_tinNo,
              cand_philhealthNo, cand_pagibigNo, cand_password, cand_createdDatetime)
              VALUES(:last_name, :first_name, :middle_name, :contact_number, :alternate_contact_number,
              :email, :alternate_email, :present_address, :permanent_address, :date_of_birth,
              :sex, :sss_number, :tin_number, :philhealth_number, :pagibig_number,
              :personal_password, :created_datetime)";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':last_name', $personalInformation['lastName']);
    $stmt->bindParam(':first_name', $personalInformation['firstName']);
    $stmt->bindParam(':middle_name', $personalInformation['middleName']);
    $stmt->bindParam(':contact_number', $personalInformation['contact']);
    $stmt->bindParam(':alternate_contact_number', $personalInformation['alternateContact']);
    $stmt->bindParam(':email', $personalInformation['email']);
    $stmt->bindParam(':alternate_email', $personalInformation['alternateEmail']);
    $stmt->bindParam(':present_address', $personalInformation['presentAddress']);
    $stmt->bindParam(':permanent_address', $personalInformation['permanentAddress']);
    $stmt->bindParam(':date_of_birth', $personalInformation['dob']);
    $stmt->bindParam(':sex', $personalInformation['gender']);
    $stmt->bindParam(':sss_number', $personalInformation['sss']);
    $stmt->bindParam(':tin_number', $personalInformation['tin']);
    $stmt->bindParam(':philhealth_number', $personalInformation['philhealth']);
    $stmt->bindParam(':pagibig_number', $personalInformation['pagibig']);
    $stmt->bindParam(':personal_password', $personalInformation['password']);
    $stmt->bindParam(':created_datetime', $createdDateTime);
    $stmt->execute();
    $newId = $conn->lastInsertId();

    if ($stmt->rowCount() > 0) {
      if (!empty($educationalBackground)) {
        $sql = "INSERT INTO tblcandeducbackground (educ_canId, educ_coursesId, educ_institutionId, educ_dateGraduate)
        VALUES (:personal_info_id, :educational_courses_id, :educational_institution_id, :educational_date_graduate)";

        foreach ($educationalBackground as $item) {
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':personal_info_id', $newId);
          $stmt->bindParam(':educational_courses_id', $item['course']);
          $stmt->bindParam(':educational_institution_id', $item['institution']);
          $stmt->bindParam(':educational_date_graduate', $item['courseDateGraduated']);
          $stmt->execute();
        }
      }

      if ($stmt->rowCount() > 0 && !empty($employmentHistory)) {
        $sql = "INSERT INTO tblcandemploymenthistory(empH_candId , empH_positionName, empH_companyName,
                      empH_startDate, empH_endDate) VALUES (:personal_info_id, :employment_position_name,
                      :employment_company_name, :employment_start_date, :employment_end_date)";
        foreach ($employmentHistory as $item) {
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':personal_info_id', $newId);
          $stmt->bindParam(':employment_position_name', $item['position']);
          $stmt->bindParam(':employment_company_name', $item['company']);
          $stmt->bindParam(':employment_start_date', $item['startDate']);
          $stmt->bindParam(':employment_end_date', $item['endDate']);
          $stmt->execute();
        }
      }

      if ($stmt->rowCount() > 0 && !empty($trainings)) {
        $sql = "INSERT INTO tblcandtraining (training_candId , training_perTId )
                      VALUES (:personal_info_id, :personal_training_id)";
        foreach ($trainings as $item) {
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':personal_info_id', $newId);
          $stmt->bindParam(':personal_training_id', $item['training']);
          $stmt->execute();
        }
      }

      if ($stmt->rowCount() > 0 && !empty($skills)) {
        $sql = "INSERT INTO tblcandskills(skills_candId , skills_perSId)
                      VALUES (:personal_info_id, :personal_skill_id)";
        foreach ($skills as $item) {
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':personal_info_id', $newId);
          $stmt->bindParam(':personal_skill_id', $item['skills']);
          $stmt->execute();
        }
      }

      if ($stmt->rowCount() > 0 && !empty($knowledge)) {
        $sql = "INSERT INTO tblcandknowledge(canknow_canId , canknow_knowledgeId)
                      VALUES (:personal_info_id, :personal_knowledge_id)";
        foreach ($knowledge as $item) {
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':personal_info_id', $newId);
          $stmt->bindParam(':personal_knowledge_id', $item['knowledge']);
          $stmt->execute();
        }
      }

      if ($stmt->rowCount() > 0 && !empty($licenses)) {
        $sql = "INSERT INTO tblcandlicense(license_number, license_canId, license_masterId)
                VALUES (:license_number, :license_canId, :license_masterId)";
        foreach ($licenses as $item) {
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':license_number', $item['licenseNumber']);
          $stmt->bindParam(':license_canId', $newId);
          $stmt->bindParam(':license_masterId', $item['license']);
          $stmt->execute();
        }
      }

      if ($stmt->rowCount() > 0 && !empty($isSubscribeToEmail)) {
        $sql = "INSERT INTO tblcandconsent(cons_candId , cons_subscribetoemailupdates)
        VALUES (:personal_info_id, :personal_subscribe_to_email)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':personal_info_id', $newId);
        $stmt->bindParam(':personal_subscribe_to_email', $isSubscribeToEmail);
        $stmt->execute();
      }

      if ($stmt->rowCount() > 0) {
        $conn->commit();
        return 1;
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
  $sql = "SELECT * FROM tblinstitution";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
}

function getCourses()
{
  include "connection.php";
  $sql = "SELECT * FROM tblcourses";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
}

function getCourseType()
{
  include "connection.php";
  $sql = "SELECT * FROM tblcoursetype";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
}

function getCourseCategory()
{
  include "connection.php";
  $sql = "SELECT * FROM tblcoursescategory";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
}

function getKnowledge(){
  include "connection.php";
  $sql = "SELECT * FROM tblpersonalknowledge";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
}

function getLicense()
{
  include "connection.php";
  $sql = "SELECT * FROM tbllicensemaster";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
}

function getPinCode($json)
{
  // {"email": "qkyusans@gmail"}
  include "connection.php";
  include "send_email.php";

  $data = json_decode($json, true);
  if (recordExists($data['email'], "tblcandidates", "cand_email")) return -1;

  $firstLetter = strtoupper(substr($data['email'], 0, 1));
  $thirdLetter = strtoupper(substr($data['email'], 2, 1));
  $pincode = $firstLetter . rand(100, 999) . $thirdLetter . rand(10000, 99999);

  $currentDateTime = new DateTime("now", new DateTimeZone('Asia/Manila'));
  $expirationDateTime = $currentDateTime->add(new DateInterval('PT15M'));
  $expirationTimestamp = $expirationDateTime->format('Y-m-d H:i:s');

  // $sql = "INSERT INTO tbl_pincode (pin_email, pin_code, pin_expiration_date) VALUES (:email, :pincode, :pin_expiration_date)";
  // $stmt = $conn->prepare($sql);
  // $stmt->bindParam(':email', $data['email']);
  // $stmt->bindParam(':pincode', $pincode);
  // $stmt->bindParam(':pin_expiration_date', $expirationTimestamp);
  // $stmt->execute();
  $sendEmail = new SendEmail();
  $sendEmail->sendEmail($data['email'], $pincode . " - Your PIN Code", "Please use the following code to complete the first step:<br /><br /> <b>$pincode</b>");

  return json_encode(["pincode" => $pincode, "expirationDate" => $expirationTimestamp]);
}
function getSkills()
{
  include "connection.php";
  $sql = "SELECT * FROM tblpersonalskills";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
}

function getTraining()
{
  include "connection.php";
  $sql = "SELECT * FROM tblpersonaltraining";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
}

function isEmailExist($json)
  {
    // {"email": "qkyusans@gmail"}
    include "connection.php";
    $data = json_decode($json, true);
    if (recordExists($data['email'], "tblcandidates", "cand_email ")) {
      return -1;
    } else {
      return 1;
    }
  }

  function getAllDataForDropdownSignup()
  {
    include "connection.php";
    $conn->beginTransaction();
    try {
      $data = [];

      $sql = "SELECT * FROM tblinstitution";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['institution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $sql = "SELECT * FROM tblcourses";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['courses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $sql = "SELECT * FROM tblcoursetype";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['courseType'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $sql = "SELECT * FROM tblpersonalskills";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['skills'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $sql = "SELECT * FROM tblpersonaltraining";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['training'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $sql = "SELECT * FROM tblpersonalknowledge";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['knowledge'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $sql = "SELECT * FROM tbllicensemaster";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['license'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $sql = "SELECT * FROM tbllicensetype";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['licenseType'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $conn->commit();

      return json_encode($data);
    } catch (\Throwable $th) {
      $conn->rollBack();
      return 0;
    }
  }

  function sendEmail($json)
  {
    include "send_email.php";
    // {"emailToSent":"xhifumine@gmail.com","emailSubject":"Kunwari MESSAGE","emailBody":"Kunwari message ni diri hehe <b>102345</b>"}
    $data = json_decode($json, true);
    $sendEmail = new SendEmail();
    return $sendEmail->sendEmail($data['emailToSent'], $data['emailSubject'], $data['emailBody']);
  }


  // function getCandidatesProfile(){
  //   include "connection.php";

  //   $sql = "SELECT a.cand_firstname, a.cand_lastname, a.cand_middlename, a.cand_contactNo, a.cand_alternatecontactNo,a.cand_email, a.cand_email, a.cand_alternateEmail, a.cand_presentAddress, a.cand_permanentAddress, a.cand_dateofBirth, a.cand_sex, a.cand_sssNo, a.cand_tinNo, a.cand_philhealthNo, a.cand_pagibigNo, a.cand_password, a.cand_createdDatetime, a.cand_updatedDatetime,
  //     GROUP_CONCAT(b.educ_name SEPARATOR '|') as educ_name


  //   FROM tblcandidates a
  //   LEFT JOIN tbleducbackground b ON a.cand_id = b.educ_candId
  //   ";
  // }


  function getActiveJob()
  {
      include "connection.php";

      $sql = "
        SELECT a.jobM_id, a.jobM_title, a.jobM_description, a.jobM_status,
               DATE_FORMAT(a.jobM_createdAt, '%b %d, %Y %h:%i %p') as jobM_createdAt,
               GROUP_CONCAT(DISTINCT c.duties_text SEPARATOR '|') as duties_text,
               GROUP_CONCAT(DISTINCT d.jeduc_text SEPARATOR '|') as jeduc_text,
               GROUP_CONCAT(DISTINCT e.jwork_responsibilities SEPARATOR '|') as jwork_responsibilities,
               GROUP_CONCAT(DISTINCT e.jwork_duration SEPARATOR '|') as jwork_duration,
               GROUP_CONCAT(DISTINCT f.jknow_text SEPARATOR '|') as jknow_text,
               GROUP_CONCAT(DISTINCT i.knowledge_name SEPARATOR '|') as knowledge_name,
               GROUP_CONCAT(DISTINCT g.jskills_text SEPARATOR '|') as jskills_text,
               GROUP_CONCAT(DISTINCT h.jtrng_text SEPARATOR '|') as jtrng_text,
               (SELECT COUNT(*)
                FROM tblapplications b
                WHERE b.app_jobMId = a.jobM_id) as Total_Applied
        FROM tbljobsmaster a
        LEFT JOIN tbljobsmasterduties c ON a.jobM_id = c.duties_jobId
        LEFT JOIN tbljobseducation d ON a.jobM_id = d.jeduc_jobId
        LEFT JOIN tbljobsworkexperience e ON a.jobM_id = e.jwork_jobId
        LEFT JOIN tbljobsknowledge f ON a.jobM_id = f.jknow_jobId
        LEFT JOIN tbljobsskills g ON a.jobM_id = g.jskills_jobId
        LEFT JOIN tbljobstrainings h ON a.jobM_id = h.jtrng_jobId
        LEFT JOIN tblpersonalknowledge i ON f.jknow_knowledgeId = i.knowledge_id
        WHERE a.jobM_status = 1
        GROUP BY a.jobM_id";


      try {
          $stmt = $conn->prepare($sql);
          $stmt->execute();

          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if (is_array($result)) {
              echo json_encode($result);
          } else {

              echo json_encode([]);
          }
      } catch (PDOException $e) {
          // Handle database errors
          echo json_encode(["error" => $e->getMessage()]);
      }
  }



  // function getAppliedJobs()
  // {
  //   include "connection.php";

  //   try {

  //     $json = file_get_contents('php://input');
  //     $data = json_decode($json, true);


  //     error_log(print_r($data, true));

  //     if (!isset($data['cand_id'])) {
  //       return json_encode(["error" => "cand_id not provided"]);
  //     }

  //     $cand_id = (int) $data['cand_id'];

  //     $sql = "SELECT a.jobM_title
  //                 FROM tbljobsmaster a
  //                 INNER JOIN tblapplications b
  //                 ON a.jobM_id  = b.posA_jobMId
  //                 WHERE b.posA_candId  = :cand_id";

  //     $stmt = $conn->prepare($sql);
  //     $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
  //     $stmt->execute();

  //     $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  //     if (empty($result)) {
  //       return json_encode(["error" => "No applied jobs found"]);
  //     }

  //     return json_encode($result);

  //   } catch (PDOException $e) {
  //     return json_encode(["error" => "Database error: " . $e->getMessage()]);
  //   }
  // }

  function getAppliedJobs() {
    include "connection.php";

    try {
        // Ensure 'cand_id' is provided in the POST request
        if (!isset($_POST['cand_id'])) {
            echo json_encode(["error" => "cand_id not provided"]);
            return;
        }

        $cand_id = (int) $_POST['cand_id'];

        // Prepare the SQL query to fetch applied jobs
        $sql = "SELECT a.jobM_title
                FROM tbljobsmaster a
                INNER JOIN tblapplications b
                ON a.jobM_id  = b.app_jobMId
                WHERE b.app_candId  = :cand_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
        $stmt->execute();

        // Fetch the results
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if any jobs were found
        if (empty($result)) {
            echo json_encode(["error" => "No applied jobs found"]);
            return;
        }

        // Return the results as JSON
        echo json_encode($result);

    } catch (PDOException $e) {
        // Return any database errors
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}


  // function applyForJob()
  // {
  //     include "connection.php";


  //     $input = json_decode(file_get_contents('php://input'), true);


  //     if (!isset($input['user_id']) || !isset($input['jobId'])) {
  //         echo json_encode(["error" => "Missing required parameters"]);
  //         return;
  //     }

  //     $user_id = $input['user_id'];
  //     $jobId = $input['jobId'];

  //     $sqlCheckJob = "SELECT jobM_id FROM tbljobsmaster WHERE jobM_id = :jobId";
  //     $stmtCheckJob = $conn->prepare($sqlCheckJob);
  //     $stmtCheckJob->bindParam(':jobId', $jobId, PDO::PARAM_INT);
  //     $stmtCheckJob->execute();

  //     if ($stmtCheckJob->rowCount() == 0) {
  //         echo json_encode(["error" => "Invalid job ID"]);
  //         return;
  //     }


  //     $sqlCheckApplication = "SELECT posA_id FROM tblapplications WHERE posA_candId = :user_id AND posA_jobMId = :jobId";
  //     $stmtCheckApplication = $conn->prepare($sqlCheckApplication);
  //     $stmtCheckApplication->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  //     $stmtCheckApplication->bindParam(':jobId', $jobId, PDO::PARAM_INT);
  //     $stmtCheckApplication->execute();

  //     if ($stmtCheckApplication->rowCount() > 0) {
  //         echo json_encode(["error" => "You have already applied for this job"]);
  //         return;
  //     }


  //     $currentDateTime = date('Y-m-d H:i:s');

  //     $sql = "
  //         INSERT INTO tblapplications (posA_candId, posA_jobMId, posA_datetime)
  //         VALUES (:user_id, :jobId, :posA_datetime)
  //     ";

  //     try {
  //         $stmt = $conn->prepare($sql);
  //         $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  //         $stmt->bindParam(':jobId', $jobId, PDO::PARAM_INT);
  //         $stmt->bindParam(':posA_datetime', $currentDateTime, PDO::PARAM_STR);
  //         $stmt->execute();

  //         echo json_encode(["success" => "Job applied successfully"]);
  //     } catch (PDOException $e) {
  //         echo json_encode(["error" => $e->getMessage()]);
  //     }
  // }

  function applyForJob()
{
    include "connection.php";

    if (!isset($_POST['user_id']) || !isset($_POST['jobId'])) {
        echo json_encode(["error" => "Missing required parameters"]);
        return;
    }

    $user_id = $_POST['user_id'];
    $jobId = $_POST['jobId'];


    $sqlCheckJob = "SELECT jobM_id FROM tbljobsmaster WHERE jobM_id = :jobId";
    $stmtCheckJob = $conn->prepare($sqlCheckJob);
    $stmtCheckJob->bindParam(':jobId', $jobId, PDO::PARAM_INT);
    $stmtCheckJob->execute();

    if ($stmtCheckJob->rowCount() == 0) {
        echo json_encode(["error" => "Invalid job ID"]);
        return;
    }


    $sqlCheckApplication = "SELECT app_id FROM tblapplications WHERE app_candId = :user_id AND app_jobMId = :jobId";
    $stmtCheckApplication = $conn->prepare($sqlCheckApplication);
    $stmtCheckApplication->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtCheckApplication->bindParam(':jobId', $jobId, PDO::PARAM_INT);
    $stmtCheckApplication->execute();

    if ($stmtCheckApplication->rowCount() > 0) {
        echo json_encode(["error" => "You have already applied for this job"]);
        return;
    }

    $sqlGetStatusId = "SELECT status_id FROM tblstatus WHERE status_name = 'Pending'";
    $stmtGetStatusId = $conn->prepare($sqlGetStatusId);
    $stmtGetStatusId->execute();
    $status = $stmtGetStatusId->fetch(PDO::FETCH_ASSOC);

    if (!$status) {
        echo json_encode(["error" => "Pending status not found"]);
        return;
    }

    $appSId = $status['status_id'];

    $currentDateTime = date('Y-m-d H:i:s');
    $sql = "
        INSERT INTO tblapplications (app_candId, app_jobMId, app_datetime, app_statusId)
        VALUES (:user_id, :jobId, :app_datetime, :appSId)
    ";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':jobId', $jobId, PDO::PARAM_INT);
        $stmt->bindParam(':app_datetime', $currentDateTime, PDO::PARAM_STR);
        $stmt->bindParam(':appSId', $appSId, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["success" => "Job applied successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}



  function getCandidateProfile($json) {
    include "connection.php";
    $returnValue = [];
    $data = json_decode($json, true);

    $cand_id = isset($data['cand_id']) ? (int) $data['cand_id'] : 0;


    $sql = "SELECT * FROM tblcandidates WHERE cand_id = :cand_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
    $stmt->execute();
    $returnValue["candidateInformation"] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];


    $sql = "SELECT b.courses_name, c.institution_name, a.educ_dategraduate, d.course_categoryName, e.crs_type_name, a.educ_back_id, b.courses_id, c.institution_id FROM tblcandeducbackground a
     INNER JOIN tblcourses b ON a.educ_coursesId = b.courses_id
     INNER JOIN tblinstitution c ON a.educ_institutionId = c.institution_id
     INNER JOIN tblcoursescategory d ON b.courses_coursecategoryId = d.course_categoryId
     INNER JOIN tblcoursetype e ON b.courses_courseTypeId = e.crs_type_id
     WHERE educ_canId = :cand_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
    $stmt->execute();
    $returnValue["educationalBackground"] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];


    $sql = "SELECT * FROM tblcandemploymenthistory
     WHERE empH_candId = :cand_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
    $stmt->execute();
    $returnValue["employmentHistory"] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];


    $sql = "SELECT b.perS_name, b.perS_id, a.skills_id, a.skills_perSId FROM tblcandskills a
     INNER JOIN tblpersonalskills b ON a.skills_perSId = b.perS_id
     WHERE skills_candId = :cand_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
    $stmt->execute();
    $returnValue["skills"] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $sql = "SELECT b.perT_name, a.training_id, b.perT_id, a.training_perTId FROM tblcandtraining a
     INNER JOIN tblpersonaltraining b ON a.training_perTId = b.perT_id
     WHERE training_candId = :cand_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
    $stmt->execute();
    $returnValue["training"] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $sql = "SELECT b.knowledge_name, a.canknow_id, a.canknow_knowledgeId FROM tblcandknowledge a
     INNER JOIN tblpersonalknowledge b ON a.canknow_knowledgeId = b.knowledge_id
     WHERE canknow_canId = :cand_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
    $stmt->execute();
    $returnValue["knowledge"] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $sql = "SELECT b.license_master_name, a.license_number, c.license_type_name, a.license_id, a.license_masterId FROM tblcandlicense a
    INNER JOIN tbllicensemaster b ON a.license_masterId = b.license_master_id
    INNER JOIN tbllicensetype c ON b.license_master_typeId = c.license_type_id
    WHERE license_canId = :cand_id";
   $stmt = $conn->prepare($sql);
   $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
   $stmt->execute();
   $returnValue["license"] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    error_log("Return Value: " . print_r($returnValue, true));

    return json_encode($returnValue);
}

function updateCandidatePersonalInfo($json) {
  include "connection.php";
  $data = json_decode($json, true);

  // Extract candidate ID from the data
  $cand_id = isset($data['cand_id']) ? (int) $data['cand_id'] : 0;

  if ($cand_id === 0) {
      return json_encode(["error" => "Invalid candidate ID"]);
  }

  try {
      // Update candidate information
      if (isset($data['candidateInformation'])) {
        $candidateInfo = $data['candidateInformation'];
        $sql = "UPDATE tblcandidates SET
                cand_firstname = :first_name,
                cand_lastname = :last_name,
                cand_email = :email,
                cand_contactNo = :contact_no,
                cand_alternatecontactNo = :alternate_contact_no,
                cand_presentAddress = :present_address,
                cand_permanentAddress = :permanent_address,
                cand_dateofBirth = :date_of_birth,
                cand_sex = :sex,
                cand_sssNo = :sss_no,
                cand_tinNo = :tin_no,
                cand_philhealthNo = :philhealth_no,
                cand_pagibigNo = :pagibig_no
                WHERE cand_id = :cand_id";

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':first_name', $candidateInfo['cand_firstname'], PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $candidateInfo['cand_lastname'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $candidateInfo['cand_email'], PDO::PARAM_STR);
        $stmt->bindParam(':contact_no', $candidateInfo['cand_contactNo'], PDO::PARAM_STR);
        $stmt->bindParam(':alternate_contact_no', $candidateInfo['cand_alternatecontactNo'], PDO::PARAM_STR);
        $stmt->bindParam(':present_address', $candidateInfo['cand_presentAddress'], PDO::PARAM_STR);
        $stmt->bindParam(':permanent_address', $candidateInfo['cand_permanentAddress'], PDO::PARAM_STR);
        $stmt->bindParam(':date_of_birth', $candidateInfo['cand_dateofBirth'], PDO::PARAM_STR);
        $stmt->bindParam(':sex', $candidateInfo['cand_sex'], PDO::PARAM_STR);
        $stmt->bindParam(':sss_no', $candidateInfo['cand_sssNo'], PDO::PARAM_STR);
        $stmt->bindParam(':tin_no', $candidateInfo['cand_tinNo'], PDO::PARAM_STR);
        $stmt->bindParam(':philhealth_no', $candidateInfo['cand_philhealthNo'], PDO::PARAM_STR);
        $stmt->bindParam(':pagibig_no', $candidateInfo['cand_pagibigNo'], PDO::PARAM_STR);
        $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);

        $stmt->execute();
    }


      // Update educational background
    //   if (isset($data['educationalBackground'])) {
    //     $education = $data['educationalBackground'];
    //     foreach ($education as $item) {
    //         if (isset($item['educ_id'])) {
    //             // Update existing record
    //             $sql = "UPDATE tblcandeducbackground SET
    //                     educ_coursesName = :courses_name,
    //                     educ_institutionName = :institution_name,
    //                     educ_dategraduate = :dategraduate
    //                     WHERE educ_id = :educ_id AND educ_canId = :cand_id";
    //             $stmt = $conn->prepare($sql);
    //             $stmt->bindParam(':courses_name', $item['courses_name'], PDO::PARAM_STR);
    //             $stmt->bindParam(':institution_name', $item['institution_name'], PDO::PARAM_STR);
    //             $stmt->bindParam(':dategraduate', $item['educ_dategraduate'], PDO::PARAM_STR);
    //             $stmt->bindParam(':educ_id', $item['educ_id'], PDO::PARAM_INT);
    //             $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
    //             $stmt->execute();
    //         } else {
    //             // Insert new record if educ_id is not present
    //             $sql = "INSERT INTO tblcandeducbackground (educ_canId, educ_coursesName, educ_institutionName, educ_dategraduate)
    //                     VALUES (:cand_id, :courses_name, :institution_name, :dategraduate)";
    //             $stmt = $conn->prepare($sql);
    //             $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
    //             $stmt->bindParam(':courses_name', $item['courses_name'], PDO::PARAM_STR);
    //             $stmt->bindParam(':institution_name', $item['institution_name'], PDO::PARAM_STR);
    //             $stmt->bindParam(':dategraduate', $item['educ_dategraduate'], PDO::PARAM_STR);
    //             $stmt->execute();
    //         }
    //     }
    // }



      // // Update employment history



      // // Update skills
      // if (isset($data['skills'])) {
      //     // Clear existing records and reinsert new ones
      //     $sql = "DELETE FROM tblcandskills WHERE skills_candId = :cand_id";
      //     $stmt = $conn->prepare($sql);
      //     $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
      //     $stmt->execute();

      //     foreach ($data['skills'] as $item) {
      //         $sql = "INSERT INTO tblcandskills (skills_candId, skills_perSId)
      //                 VALUES (:cand_id, :perSId)";
      //         $stmt = $conn->prepare($sql);
      //         $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
      //         $stmt->bindParam(':perSId', $item['perS_id'], PDO::PARAM_INT);
      //         $stmt->execute();
      //     }
      // }

      // // Update training
      // if (isset($data['training'])) {
      //     // Clear existing records and reinsert new ones
      //     $sql = "DELETE FROM tblcandtraining WHERE training_candId = :cand_id";
      //     $stmt = $conn->prepare($sql);
      //     $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
      //     $stmt->execute();

      //     foreach ($data['training'] as $item) {
      //         $sql = "INSERT INTO tblcandtraining (training_candId, training_perTId)
      //                 VALUES (:cand_id, :perTId)";
      //         $stmt = $conn->prepare($sql);
      //         $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
      //         $stmt->bindParam(':perTId', $item['perT_id'], PDO::PARAM_INT);
      //         $stmt->execute();
      //     }
      // }

      // // Update knowledge
      // if (isset($data['knowledge'])) {
      //     // Clear existing records and reinsert new ones
      //     $sql = "DELETE FROM tblcandknowledge WHERE canknow_canId = :cand_id";
      //     $stmt = $conn->prepare($sql);
      //     $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
      //     $stmt->execute();

      //     foreach ($data['knowledge'] as $item) {
      //         $sql = "INSERT INTO tblcandknowledge (canknow_canId, canknow_knowledgeId)
      //                 VALUES (:cand_id, :knowledgeId)";
      //         $stmt = $conn->prepare($sql);
      //         $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
      //         $stmt->bindParam(':knowledgeId', $item['knowledge_id'], PDO::PARAM_INT);
      //         $stmt->execute();
      //     }
      // }

      // // Update license
      // if (isset($data['license'])) {
      //     // Clear existing records and reinsert new ones
      //     $sql = "DELETE FROM tblcandlicense WHERE license_canId = :cand_id";
      //     $stmt = $conn->prepare($sql);
      //     $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
      //     $stmt->execute();

      //     foreach ($data['license'] as $item) {
      //         $sql = "INSERT INTO tblcandlicense (license_canId, license_masterId, license_number)
      //                 VALUES (:cand_id, :masterId, :number)";
      //         $stmt = $conn->prepare($sql);
      //         $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
      //         $stmt->bindParam(':masterId', $item['license_masterId'], PDO::PARAM_INT);
      //         $stmt->bindParam(':number', $item['license_number'], PDO::PARAM_STR);
      //         $stmt->execute();
      //     }
      // }

      return json_encode(["success" => "Profile updated successfully"]);

  } catch (PDOException $e) {
      return json_encode(["error" => $e->getMessage()]);
  }
}

function updateEducationalBackground($json)
  {
    // {"candidateId": 21, "educationalBackground": [{"educId": 10, "courseId": 25, "institutionId": 1, "courseDateGraduated": "2022-01-01"}]}


    // if nag add siyag bag-o ------------------------eh null ang educId
    // {"candidateId": 21, "educationalBackground": [{"educId": null, "courseId": 25, "institutionId": 1, "courseDateGraduated": "2022-01-01"}]}


    include "connection.php";
    $conn->beginTransaction();
    try {
      $json = json_decode($json, true);
      $candidateId = $json['cand_id'] ?? 0;
      $educationalBackground = $json['educationalBackground'] ?? [];

      if (!empty($educationalBackground)) {
        foreach ($educationalBackground as $item) {
          if (isset($item['educId']) && !empty($item['educId'])) {

            $sql = "SELECT educ_back_id FROM tblcandeducbackground WHERE educ_back_id = :educ_back_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':educ_back_id', $item['educId']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {

              $sql = "UPDATE tblcandeducbackground
                                  SET educ_coursesId = :educational_courses_id,
                                      educ_institutionId = :educational_institution_id,
                                      educ_dateGraduate = :educational_date_graduate
                                  WHERE educ_back_id = :educ_back_id";
              $stmt = $conn->prepare($sql);
              $stmt->bindParam(':educational_courses_id', $item['courseId']);
              $stmt->bindParam(':educational_institution_id', $item['institutionId']);
              $stmt->bindParam(':educational_date_graduate', $item['courseDateGraduated']);
              $stmt->bindParam(':educ_back_id', $item['educId']);
              $stmt->execute();
            }
          } else {

            $sql = "INSERT INTO tblcandeducbackground (educ_canId, educ_coursesId, educ_institutionId, educ_dateGraduate)
                              VALUES (:personal_info_id, :educational_courses_id, :educational_institution_id, :educational_date_graduate)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':personal_info_id', $candidateId);
            $stmt->bindParam(':educational_courses_id', $item['courseId']);
            $stmt->bindParam(':educational_institution_id', $item['institutionId']);
            $stmt->bindParam(':educational_date_graduate', $item['courseDateGraduated']);
            $stmt->execute();
          }
        }
      }



        $conn->commit();
        return 1;

    } catch (PDOException $th) {
      $conn->rollBack();
      return 0;
    }
  }


function updateCandidateEmploymentInfo($json){
  include "connection.php";
  $data = json_decode($json, true);
  $cand_id = isset($data['cand_id']) ? (int) $data['cand_id'] : 0;

  try{
    if (isset($data['employmentHistory'])) {

      $sql = "DELETE FROM tblcandemploymenthistory WHERE empH_candId = :cand_id";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
      $stmt->execute();

      foreach ($data['employmentHistory'] as $item) {

          $sql = "INSERT INTO tblcandemploymenthistory (empH_candId, empH_positionName, empH_companyName, empH_startdate, empH_enddate)
                  VALUES (:cand_id, :position_name, :company_name, :start_date, :end_date)";
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':cand_id', $cand_id, PDO::PARAM_INT);
          $stmt->bindParam(':position_name', $item['empH_positionName'], PDO::PARAM_STR);
          $stmt->bindParam(':company_name', $item['empH_companyName'], PDO::PARAM_STR);
          $stmt->bindParam(':start_date', $item['empH_startdate'], PDO::PARAM_STR);
          $stmt->bindParam(':end_date', $item['empH_enddate'], PDO::PARAM_STR);
          $stmt->execute();
      }
      return json_encode(["success" => "Educational updated successfully"]);
  }
  }catch(PDOException $e){
    return json_encode(["error" => $e->getMessage()]);
  }

}

function updateCandidateSkills($json) {
  include "connection.php";
  $conn->beginTransaction();
  try {
    $json = json_decode($json, true);
    $candidateId = $json['cand_id'] ?? 0;
    $skills = $json['skills'] ?? [];

    if (!empty($skills)) {
      foreach ($skills as $item) {
        if (isset($item['skillId']) && !empty($item['skillId'])) {

          $sql = "SELECT skills_id FROM tblcandskills WHERE skills_id = :skills_id";
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':skills_id', $item['skills_id']);
          $stmt->execute();

          if ($stmt->rowCount() > 0) {

            $sql = "UPDATE tblcandskills
                    SET skills_perSId = :skills_perSId
                    WHERE skills_id = :skills_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':skills_perSId', $item['skillId']);
            $stmt->bindParam(':skills_id', $item['skills_id']);
            $stmt->execute();
          }
        } else {

          $sql = "INSERT INTO tblcandskills (skills_candId, skills_perSId)
                  VALUES (:skills_candId, :skills_perSId)";
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':skills_candId', $candidateId);
          $stmt->bindParam(':skills_perSId', $item['skills_perSId']);
        }
      }
    }

    $conn->commit();
    return 1;
  } catch (PDOException $e) {
    $conn->rollBack();
    error_log("Error updating skills: " . $e->getMessage()); // Log the error for debugging
    return 0;
  }
}

function updateCandidateTraining($json) {
  include "connection.php";
  $conn->beginTransaction();
  try {
      // Decode JSON
      $json = json_decode($json, true);
      $candidateId = $json['cand_id'] ?? 0;
      $trainings = $json['training'] ?? []; // Change to 'training' to match frontend data structure

      if (!empty($trainings)) {
          foreach ($trainings as $item) {
              if (isset($item['training_id']) && !empty($item['training_id'])) {
                  $trainingId = $item['training_id'];
                  $perTId = $item['perT_id']; // Use 'perT_id' to match frontend data

                  // Check if training exists
                  $sql = "SELECT training_id FROM tblcandtraining WHERE training_id = :training_id";
                  $stmt = $conn->prepare($sql);
                  $stmt->bindParam(':training_id', $trainingId);
                  $stmt->execute();

                  if ($stmt->rowCount() > 0) {
                      // Update existing training
                      $sql = "UPDATE tblcandtraining
                              SET training_perTId = :training_perTId
                              WHERE training_id = :training_id";
                      $stmt = $conn->prepare($sql);
                      $stmt->bindParam(':training_perTId', $perTId);
                      $stmt->bindParam(':training_id', $trainingId);
                      $stmt->execute();
                  } else {
                      // Insert new training
                      $sql = "INSERT INTO tblcandtraining (training_candId, training_perTId)
                              VALUES (:training_candId, :training_perTId)";
                      $stmt = $conn->prepare($sql);
                      $stmt->bindParam(':training_candId', $candidateId);
                      $stmt->bindParam(':training_perTId', $perTId);
                      $stmt->execute();
                  }
              }
          }
      }

      $conn->commit();
      return 1; // Return success
  } catch (PDOException $e) {
      $conn->rollBack();
      error_log("Error updating training: " . $e->getMessage()); // Log the error for debugging
      return 0; // Return failure
  }
}



function updateCandidateKnowledge($json) {
  include "connection.php";
  $conn->beginTransaction();
  try {

      $json = json_decode($json, true);
      $candidateId = $json['cand_id'] ?? 0;
      $knowledge = $json['knowledge'] ?? [];

      if (!empty($knowledge)) {
          foreach ($knowledge as $item) {
              if (isset($item['canknow_id']) && !empty($item['canknow_id'])) {
                  $canknow_id = $item['canknow_id'];
                  $knowledge_id = $item['knowledge_id'];

                  $sql = "SELECT canknow_id FROM tblcandknowledge WHERE canknow_id = :canknow_id";
                  $stmt = $conn->prepare($sql);
                  $stmt->bindParam(':canknow_id', $canknow_id);
                  $stmt->execute();

                  if ($stmt->rowCount() > 0) {
                      // Update existing knowledge
                      $sql = "UPDATE tblcandknowledge
                              SET canknow_knowledgeId = :knowledge_id
                              WHERE canknow_id = :canknow_id";
                      $stmt = $conn->prepare($sql);
                      $stmt->bindParam(':knowledge_id', $knowledge_id);
                      $stmt->bindParam(':canknow_id', $canknow_id);
                      $stmt->execute();
                  } else {
                      // Insert new knowledge
                      $sql = "INSERT INTO tblcandknowledge (canknow_canId, canknow_knowledgeId)
                              VALUES (:canknow_canId, :knowledge_id)";
                      $stmt = $conn->prepare($sql);
                      $stmt->bindParam(':canknow_canId', $candidateId);
                      $stmt->bindParam(':knowledge_id', $knowledge_id);
                      $stmt->execute();
                  }
              }
          }
      }

      $conn->commit();
      return 1; // Return success
  } catch (PDOException $e) {
      $conn->rollBack();
      error_log("Error updating knowledge: " . $e->getMessage()); // Log the error for debugging
      return 0; // Return failure
  }
}



function updateCandidateLicense($json)
{
    include "connection.php";
    $conn->beginTransaction();
    try {
        $json = json_decode($json, true);
        $candidateId = $json['cand_id'] ?? 0;
        $license = $json['license'] ?? [];

        if (!empty($license)) {
            foreach ($license as $item) {
                if (isset($item['license_id']) && !empty($item['license_id'])) {
                    // Check if license exists
                    $sql = "SELECT license_id FROM tblcandlicense WHERE license_id = :license_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':license_id', $item['license_id'], PDO::PARAM_INT);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {

                        $sql = "UPDATE tblcandlicense
                                SET license_masterId = :license_masterId,
                                    license_number = :license_number
                                WHERE license_id = :license_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':license_masterId', $item['license_masterId'], PDO::PARAM_INT);
                        $stmt->bindParam(':license_number', $item['license_number'], PDO::PARAM_STR);
                        $stmt->bindParam(':license_id', $item['license_id'], PDO::PARAM_INT);
                        $stmt->execute();
                    }
                } else {
                    // Insert new license record
                    $sql = "INSERT INTO tblcandlicense (license_canId, license_masterId, license_number)
                            VALUES (:license_canId, :license_masterId, :license_number)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':license_canId', $candidateId, PDO::PARAM_INT);
                    $stmt->bindParam(':license_masterId', $item['license_masterId'], PDO::PARAM_INT);
                    $stmt->bindParam(':license_number', $item['license_number'], PDO::PARAM_STR); // Assuming license number can be alphanumeric
                    $stmt->execute();
                }
            }
        }


            $conn->commit();
            return 1;

    } catch (PDOException $th) {
        $conn->rollBack();
        return 0;
    }
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

// $input = json_decode(file_get_contents('php://input'), true);



$operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";
$json = isset($_POST["json"]) ? $_POST["json"] : "0";

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
  case "getCourseType":
    echo $user->getCourseType();
    break;
  case "sendEmail":
    echo $user->sendEmail($json);
    break;
  case "getActiveJob":
    echo $user->getActiveJob();
    break;
  case "getAppliedJobs":
    echo $user->getAppliedJobs();
    break;
  case "applyForJob":
    echo $user->applyForJob();
    break;
  case "getPinCode":
    echo $user->getPinCode($json);
    break;
  case "getSkills":
    echo $user->getSkills();
    break;
  case "getTraining":
    echo $user->getTraining();
    break;
  case "getLicense":
    echo $user->getLicense();
    break;
  case "getKnowledge":
    echo $user->getKnowledge();
    break;
  case "isEmailExist":
    echo $user->isEmailExist($json);
    break;

  case "getAllDataForDropdownSignup":
    echo $user->getAllDataForDropdownSignup();
    break;
  case "getCandidateProfile":
    echo $user->getCandidateProfile($json);
    break;
  case "updateCandidatePersonalInfo":
    echo $user->updateCandidatePersonalInfo($json);
    break;
  case "updateEducationalBackground":
    echo $user->updateEducationalBackground($json);
    break;
  case "updateCandidateEmploymentInfo":
    echo $user->updateCandidateEmploymentInfo($json);
    break;
  case "updateCandidateSkills":
    echo $user->updateCandidateSkills($json);
    break;
  case "getCourseCategory":
    echo $user->getCourseCategory();
    break;
  case "updateCandidateTraining":
    echo $user->updateCandidateTraining($json);
    break;
  case "updateCandidateKnowledge":
    echo $user->updateCandidateKnowledge($json);
    break;
  case "updateCandidateLicense":
    echo $user->updateCandidateLicense($json);
    break;
  default:
    echo json_encode("WALA KA NAGBUTANG OG OPERATION SA UBOS HAHAHHA BOBO");
    http_response_code(400); // Bad Request
    break;
}

