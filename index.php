<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); 

$grade = isset($_GET['grade']) ? intval($_GET['grade']) : 0;
$class = isset($_GET['class']) ? intval($_GET['class']) : 0;

if ($grade < 1 || $grade > 3) {
    echo json_encode([
        'error' => true,
        'message' => '학년은 1-3 사이여야 합니다.',
        'code' => 'INVALID_GRADE'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if ($class < 1 || $class > 20) {
    echo json_encode([
        'error' => true,
        'message' => '반은 1-20 사이여야 합니다.',
        'code' => 'INVALID_CLASS'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function fetchTimetableData() {
    $apiUrl = '';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode != 200 || empty($response)) {
        return null;
    }
    
    $lastBrace = strrpos($response, '}');
    if ($lastBrace === false) {
        return null;
    }
    
    $jsonData = substr($response, 0, $lastBrace + 1);
    return json_decode($jsonData, true);
}

$rawData = fetchTimetableData();

if (!$rawData) {
    echo json_encode([
        'error' => true,
        'message' => 'API에서 데이터를 가져올 수 없습니다.',
        'code' => 'API_ERROR'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function Q자료($m) {
    return isset($m) ? $m : 0;
}

function mTh($mm, $m2) {
    if ($m2 == 100) return floor($mm / $m2);
    return $mm % $m2;
}

function mSb($mm, $m2) {
    if ($m2 == 100) return $mm % $m2;
    return floor($mm / $m2);
}

function convertToJSON($rawData, $grade, $class) {
    $분리 = isset($rawData['분리']) ? $rawData['분리'] : 100;
    $요일명 = ['월요일', '화요일', '수요일', '목요일', '금요일'];
    
    $timetable = [
        'success' => true,
        'school' => '학교명', 
        'grade' => $grade,
        'class' => $class,
        'lastModified' => isset($rawData['자료244']) ? $rawData['자료244'] : null,
        'startDate' => isset($rawData['시작일']) ? $rawData['시작일'] : null,
        'schedule' => []
    ];
    
    for ($day = 1; $day <= 5; $day++) {
        $daySchedule = [
            'day' => $요일명[$day - 1],
            'dayNumber' => $day,
            'periods' => []
        ];
        
        for ($period = 1; $period <= 8; $period++) {
            $원자료 = Q자료($rawData['자료481'][$grade][$class][$day][$period] ?? null);
            $일일자료 = Q자료($rawData['자료147'][$grade][$class][$day][$period] ?? null);
            
            $periodData = [
                'period' => $period,
                'periodName' => isset($rawData['일과시간'][$period - 1]) ? $rawData['일과시간'][$period - 1] : $period . '교시',
                'subject' => null,
                'teacher' => null,
                'room' => null,
                'changed' => $원자료 !== $일일자료
            ];
            
            if ($일일자료 > 100) {
                $th = mTh($일일자료, $분리);
                $sb = mSb($일일자료, $분리) % $분리;
                
                if (isset($rawData['자료492'][$sb])) {
                    $periodData['subject'] = $rawData['자료492'][$sb];
                }
                
                if (isset($rawData['자료446'][$th])) {
                    $periodData['teacher'] = $rawData['자료446'][$th];
                }
                
                if (isset($rawData['강의실']) && $rawData['강의실'] == 1) {
                    $m3 = $rawData['자료245'][$grade][$class][$day][$period] ?? null;
                    if ($m3 && strpos($m3, '_') !== false) {
                        $m2 = explode('_', $m3);
                        $호수 = intval($m2[0]);
                        if ($호수 > 0 && isset($m2[1])) {
                            $periodData['room'] = $m2[1];
                        }
                    }
                }
            }
            
            $daySchedule['periods'][] = $periodData;
        }
        
        $timetable['schedule'][] = $daySchedule;
    }
    
    return $timetable;
}

$result = convertToJSON($rawData, $grade, $class);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
