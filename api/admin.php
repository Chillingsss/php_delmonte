<?php
include "headers.php";

class Admin
{
  function addJobMaster($json)
  {
    include "connection.php";
    $conn->beginTransaction();
    $data = json_decode($json, true);

    $jobMaster = $data['jobMaster'];
    $jobMasterDuties = $data['jobMasterDuties'];
    $jobEducation = $data['jobEducation'];
    $jobTraining = $data['jobTraining'];
    $jobKnowledge = $data['jobKnowledge'];
    $jobSkills = $data['jobSkill'];
    $jobWorkExperience = $data['jobExperience'];
    $todayDate = getCurrentDate();

    try {
      $sql = "INSERT INTO tbljobsmaster (jobM_title, jobM_description, jobM_status, jobM_createdAt) VALUES (:jobM_title, :jobM_description, :jobM_status, :jobM_createdAt)";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":jobM_title", $jobMaster['title']);
      $stmt->bindParam(":jobM_description", $jobMaster['description']);
      $stmt->bindParam(":jobM_status", $jobMaster['isJobActive']);
      $stmt->bindParam(":jobM_createdAt", $todayDate);
      $stmt->execute();

      $jobMasterId = $conn->lastInsertId();

      $sql = "INSERT INTO tbljobpassing (passing_jobId, passing_points) VALUES (:passing_jobId, :passing_points)";
      $stmt = $conn->prepare($sql);
      $stmt->bindParam(":passing_jobId", $jobMasterId);
      $stmt->bindParam(":passing_points", $jobMaster['passingPercentage']);
      $stmt->execute();

      $sql = "INSERT INTO tbljobsmasterduties (duties_jobId, duties_text) VALUES (:duties_jobId, :duties_text)";
      foreach ($jobMasterDuties as $duty) {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":duties_jobId", $jobMasterId);
        $stmt->bindParam(":duties_text", $duty['duties']);
        $stmt->execute();
      }

      $sql = "INSERT INTO tbljobseducation (jeduc_jobId, jeduc_text, jeduc_categoryId, jeduc_points) VALUES (:jeduc_jobId, :jeduc_text, :jeduc_categoryId, :points)";
      foreach ($jobEducation as $education) {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":jeduc_jobId", $jobMasterId);
        $stmt->bindParam(":jeduc_text", $education['jobEducation']);
        $stmt->bindParam(":jeduc_categoryId", $education['courseCategory']);
        $stmt->bindParam(":points", $education['points']);
        $stmt->execute();
      }

      $sql = "INSERT INTO tbljobstrainings (jtrng_jobId, jtrng_text, jtrng_trainingId, jtrng_points) VALUES (:jtrng_jobId, :jtrng_text, :jtrng_trainingId, :points)";
      foreach ($jobTraining as $training) {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":jtrng_jobId", $jobMasterId);
        $stmt->bindParam(":jtrng_text", $training['jobTraining']);
        $stmt->bindParam(":jtrng_trainingId", $training['training']);
        $stmt->bindParam(":points", $training['points']);
        $stmt->execute();
      }

      $sql = "INSERT INTO tbljobsknowledge (jknow_jobId, jknow_text, jknow_knowledgeId, jknow_points) VALUES (:jknow_jobId, :jknow_text, :jknow_knowledgeId, :points)";
      // $sql = "INSERT INTO tbljobsknowledge (jknow_jobId, jknow_text) VALUES (:jknow_jobId, :jknow_text)";
      foreach ($jobKnowledge as $knowledge) {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":jknow_jobId", $jobMasterId);
        $stmt->bindParam(":jknow_text", $knowledge['jobKnowledge']);
        $stmt->bindParam(":jknow_knowledgeId", $knowledge['knowledgeId']);
        $stmt->bindParam(":points", $knowledge['points']);
        $stmt->execute();
      }

      $sql = "INSERT INTO tbljobsskills (jskills_jobId, jskills_text, jskills_skillsId, jskills_points) VALUES (:jskills_jobId, :jskills_text, :jskills_skillsId, :points)";
      foreach ($jobSkills as $skill) {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":jskills_jobId", $jobMasterId);
        $stmt->bindParam(":jskills_text", $skill['jobSkill']);
        $stmt->bindParam(":jskills_skillsId", $skill['skill']);
        $stmt->bindParam(":points", $skill['points']);
        $stmt->execute();
      }

      $sql = "INSERT INTO tbljobsworkexperience (jwork_jobId, jwork_duration, jwork_responsibilities, jwork_points) VALUES (:jwork_jobId, :jwork_duration, :jwork_responsibilities, :points)";
      foreach ($jobWorkExperience as $experience) {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":jwork_jobId", $jobMasterId);
        $stmt->bindParam(":jwork_duration", $experience['yearsOfExperience']);
        $stmt->bindParam(":jwork_responsibilities", $experience['jobExperience']);
        $stmt->bindParam(":points", $experience['points']);
        $stmt->execute();
      }
      $conn->commit();
      return 1;
    } catch (PDOException $th) {
      $conn->rollBack();
      return $th;
    }
  }

  function getAllJobs()
  {
    include "connection.php";
    $sql = "SELECT a.*, COUNT(b.app_id ) as Total_Applied
              FROM tbljobsmaster a
              LEFT JOIN tblapplications b
              ON a.jobM_id = b.app_jobMId
              GROUP BY a.	jobM_id
              ORDER BY a.jobM_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
  }

  function getDropDownForAddJobs()
  {
    try {
      $data = [];
      include "connection.php";
      $conn->beginTransaction();
      $sql = "SELECT * FROM tblcoursescategory";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['courseCategory'] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;

      $sql = "SELECT * FROM tblpersonaltraining";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['personalTraining'] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;

      $sql = "SELECT * FROM tblpersonalskills";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['personalSkills'] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;

      $sql = "SELECT * FROM tblpersonalknowledge";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['knowledge'] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;

      $conn->commit();
      return json_encode($data);
    } catch (PDOException $th) {
      $conn->rollBack();
      return 0;
    }
  }

  function getSelectedJobs($json)
  {
    // {"jobId": 11}
    include "connection.php";
    $returnValue = [];
    $data = json_decode($json, true);
    $sql = "SELECT * FROM tbljobsmaster WHERE jobM_id = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    $returnValue["jobMaster"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tbljobsmasterduties WHERE duties_jobId  = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    $returnValue["jobDuties"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tbljobseducation WHERE jeduc_jobId  = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    $returnValue["jobEducation"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tbljobstrainings WHERE jtrng_jobId  = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    $returnValue["jobTrainings"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tbljobsknowledge WHERE jknow_jobId  = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    $returnValue["jobKnowledge"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tbljobsskills WHERE jskills_jobId  = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    $returnValue["jobSkills"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tbljobsworkexperience WHERE jwork_jobId  = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    $returnValue["jobExperience"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT passing_points as passing_percentage FROM tbljobpassing WHERE passing_jobId  = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    $returnValue["jobPassing"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT b.cand_id, CONCAT(b.cand_lastname, ', ', b.cand_firstname, ' ', b.cand_middlename) as FullName FROM tblapplications a
            INNER JOIN tblcandidates b ON a.app_candId = b.cand_id
            WHERE a.app_jobMId = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    $returnValue["candidates"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    foreach ($returnValue["candidates"] as &$candidate) {
      $candidate['points'] = calculateCandidatePoints($candidate['cand_id'], $data['jobId']);
    }
    return json_encode($returnValue);
  }

  function getLookUpTables()
  {
    include "connection.php";
    $returnValue = [];
    $sql = "SELECT * FROM tblcourses";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $returnValue["courses"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tblcoursescategory";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $returnValue["courseCategory"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tblinstitution";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $returnValue["institution"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tblpersonalskills";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $returnValue["skills"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $sql = "SELECT * FROM tblpersonaltraining";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $returnValue["training"] = $stmt->rowCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    return json_encode($returnValue);
  }

  function handleJobStatusSwitch($json)
  {
    // {"status": 1, "jobId": 10}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "UPDATE tbljobsmaster SET jobM_status = :status WHERE jobM_id = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":status", $data['status']);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? 1 : 0;
  }

  function getAllDataForDropdownUpdate()
  {
    include "connection.php";
    $conn->beginTransaction();
    try {
      $data = [];

      $sql = "SELECT * FROM tblcoursescategory";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $data['courseCategory'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

      $conn->commit();

      return json_encode($data);
    } catch (\Throwable $th) {
      $conn->rollBack();
      return 0;
    }
  }

  function getDuties($json)
  {
    // {"jobId": 10}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "SELECT * FROM tbljobsmasterduties WHERE duties_jobId = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
  }

  function addDuties($json)
  {
    // {"duties": "duties", "jobId": 3}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "INSERT INTO tbljobsmasterduties (duties_jobId, duties_text) VALUES (:jobId, :duties)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":duties", $data['duties']);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? 1 : 0;
  }

  function updateDuties($json)
  {
    // {"duties": "duties", "dutyId": 3}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "UPDATE tbljobsmasterduties SET duties_text = :duties WHERE duties_id = :dutyId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":duties", $data['duties']);
    $stmt->bindParam(":dutyId", $data['dutyId']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? 1 : 0;
  }

  function deleteDuties($json)
  {
    // {"dutyId": 3}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "DELETE FROM tbljobsmasterduties WHERE duties_id = :dutyId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":dutyId", $data['dutyId']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? 1 : 0;
  }

  function getJobEducation($json)
  {
    // {"jobId": 10}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "SELECT * FROM tbljobseducation WHERE jeduc_jobId = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
  }

  function addJobEducation($json)
  {
    // {"points": 10, "courseCategory": 3, "jobEducation": "jobEducation", "jobId": 11}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "INSERT INTO tbljobseducation (jeduc_points, jeduc_categoryId, jeduc_jobId, jeduc_text) VALUES (:points, :courseCategory, :jobId, :jobEducation)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":points", $data['points']);
    $stmt->bindParam(":courseCategory", $data['courseCategory']);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->bindParam(":jobEducation", $data['jobEducation']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? 1 : 0;
  }

  function updateJobEducation($json){
    // {"points": 10, "courseCategory": 3, "educationText": "jobEducation", "jobId": 11, "id": 1}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "UPDATE tbljobseducation SET jeduc_points = :points, jeduc_categoryId = :courseCategory, jeduc_text = :educationText WHERE jeduc_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":points", $data['points']);
    $stmt->bindParam(":courseCategory", $data['courseCategory']);
    $stmt->bindParam(":educationText", $data['educationText']);
    $stmt->bindParam(":id", $data['id']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? 1 : 0;
  }

  function deleteJobEducation($json){
    // {"id": 1}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "DELETE FROM tbljobseducation WHERE jeduc_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $data['id']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? 1 : 0;
  }

  function getJobSkills($json){
    // {"jobId": 10}
    include "connection.php";
    $data = json_decode($json, true);
    $sql = "SELECT * FROM tbljobsskills WHERE jskills_jobId = :jobId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":jobId", $data['jobId']);
    $stmt->execute();
    return $stmt->rowCount() > 0 ? json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)) : 0;
  }
} //admin

function calculateCandidatePoints($candId, $jobId)
{
  include "connection.php";
  $totalPoints = 0;
  $maxPoints = 0;

  $sql = "SELECT SUM(c.jeduc_points) as educ_points, (SELECT SUM(jeduc_points) FROM tbljobseducation WHERE jeduc_jobId = :jobId) as max_educ_points
          FROM tblcourses a
          INNER JOIN tblcoursescategory b ON b.course_categoryId = a.courses_coursecategoryId
          INNER JOIN tbljobseducation c ON c.jeduc_categoryId = b.course_categoryId
          INNER JOIN tblcandeducbackground d ON d.educ_coursesId = a.courses_id
          WHERE d.educ_canId = :candId AND c.jeduc_jobId = :jobId";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":candId", $candId);
  $stmt->bindParam(":jobId", $jobId);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $educationPoints = $result['educ_points'] ?? 0;
  $maxEducationPoints = $result['max_educ_points'] ?? 0;
  $totalPoints += $educationPoints;
  $maxPoints += $maxEducationPoints;

  $sql = "SELECT SUM(a.jwork_points) AS exp_points,
          (SELECT SUM(jwork_points) FROM tbljobsworkexperience WHERE jwork_jobId = :jobId) AS max_exp_points
          FROM tbljobsworkexperience a
          INNER JOIN tblapplications b ON b.app_jobMId = a.jwork_jobId
          INNER JOIN tblcandemploymenthistory c ON c.empH_candId = b.app_candId
          WHERE INSTR(a.jwork_responsibilities, c.empH_positionName) > 0
          AND c.empH_candId = :candId AND a.jwork_jobId = :jobId
          AND TIMESTAMPDIFF(YEAR, c.empH_startDate, c.empH_endDate) >= a.jwork_duration";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":candId", $candId);
  $stmt->bindParam(":jobId", $jobId);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $experiencePoints = $result['exp_points'] ?? 0;
  $maxExperiencePoints = $result['max_exp_points'] ?? 0;
  $totalPoints += $experiencePoints;
  $maxPoints += $maxExperiencePoints;

  $sql = "SELECT SUM(jskills_points) as skills_points, (SELECT SUM(jskills_points) FROM tbljobsskills WHERE jskills_jobId = :jobId) as max_skills_points
            FROM tbljobsskills j
            INNER JOIN tblcandskills c
            ON j.jskills_skillsId = c.skills_perSId
            WHERE c.skills_candId = :candId AND j.jskills_jobId = :jobId";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":candId", $candId);
  $stmt->bindParam(":jobId", $jobId);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $skillsPoints = $result['skills_points'] ?? 0;
  $maxSkillsPoints = $result['max_skills_points'] ?? 0;
  $totalPoints += $skillsPoints;
  $maxPoints += $maxSkillsPoints;

  $sql = "SELECT SUM(jtrng_points) as training_points, (SELECT SUM(jtrng_points) FROM tbljobstrainings WHERE jtrng_jobId = :jobId) as max_training_points
            FROM tbljobstrainings j
            INNER JOIN tblcandtraining c
            ON j.jtrng_trainingId = c.training_perTId
            WHERE c.training_candId = :candId AND j.jtrng_jobId = :jobId";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":candId", $candId);
  $stmt->bindParam(":jobId", $jobId);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $trainingPoints = $result['training_points'] ?? 0;
  $maxTrainingPoints = $result['max_training_points'] ?? 0;
  $totalPoints += $trainingPoints;
  $maxPoints += $maxTrainingPoints;

  $sql = "SELECT SUM(jknow_points) as knowledge_points, (SELECT SUM(jknow_points) FROM tbljobsknowledge WHERE jknow_jobId = :jobId) as max_knowledge_points
            FROM tbljobsknowledge j
            INNER JOIN tblcandknowledge c
            ON j.jknow_knowledgeId = c.canknow_knowledgeId
            WHERE c.canknow_canId = :candId AND j.jknow_jobId = :jobId";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":candId", $candId);
  $stmt->bindParam(":jobId", $jobId);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $knowledgePoints = $result['knowledge_points'] ?? 0;
  $maxKnowledgePoints = $result['max_knowledge_points'] ?? 0;
  $totalPoints += $knowledgePoints;
  $maxPoints += $maxKnowledgePoints;

  $percentage = ($maxPoints > 0) ? round(($totalPoints / $maxPoints) * 100, 2) : 0;

  return [
    'maxPoints' => $maxPoints,
    'totalPoints' => $totalPoints,
    'percentage' => $percentage,
  ];
}

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
          $fileDestination =  'images/' . $fileNameNew;
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

$json = isset($_POST["json"]) ? $_POST["json"] : "0";
$operation = isset($_POST["operation"]) ? $_POST["operation"] : "0";

$admin = new Admin();

switch ($operation) {
  case "addJobMaster":
    echo $admin->addJobMaster($json);
    break;
  case "getAllJobs":
    echo $admin->getAllJobs();
    break;
  case "getDropDownForAddJobs":
    echo $admin->getDropDownForAddJobs();
    break;
  case "getSelectedJobs":
    echo $admin->getSelectedJobs($json);
    break;
  case "getLookUpTables":
    echo $admin->getLookUpTables();
    break;
  case "handleJobStatusSwitch":
    echo $admin->handleJobStatusSwitch($json);
    break;
  case "getAllDataForDropdownUpdate":
    echo $admin->getAllDataForDropdownUpdate();
    break;
  case "getDuties":
    echo $admin->getDuties($json);
    break;
  case "addDuties":
    echo $admin->addDuties($json);
    break;
  case "updateDuties":
    echo $admin->updateDuties($json);
    break;
  case "deleteDuties":
    echo $admin->deleteDuties($json);
    break;
  case "getJobEducation":
    echo $admin->getJobEducation($json);
    break;
  case "addJobEducation":
    echo $admin->addJobEducation($json);
    break;
  case "updateJobEducation":
    echo $admin->updateJobEducation($json);
    break;
  case "deleteJobEducation":
    echo $admin->deleteJobEducation($json);
    break;
  case "getJobSkills":
    echo $admin->getJobSkills($json);
    break;
  default:
    echo "WALA KA NAGBUTANG OG OPERATION SA UBOS HAHAHHA BOBO";
    break;
}