# 컴시간 알리미 API 
컴시간 알리미를 리버스 엔지니어링하여 API를 파싱하고 편리하게 사용할 수 있도록 JSON 형식의 API로 재구성합니다.

## 가이드
### API 엔드포인트 추출
컴시간 알리미의 시간표를 불러오는 API 주소는 이러합니다.
```
http://comci.net:4082/36179
```
아래 양식에 학교 코드를 넣어 BASE64로 인코딩하고 주소 뒤 GET 방식으로 추가합니다.
```
BASE64 - NzM2MjlfNTM3MDZfMF8x
원본 - 73629_53706_0_1
양식 - 73629_{학교 코드}_0_1
```
학교 코드를 확인하는 방법은 컴시간 알리미 사이트에서 학교를 설정한 뒤 localStorage의 ```sc``` 값을 확인하면 됩니다.
#### 최종 API 엔드포인트
```
http://comci.net:4082/36179?NzM2MjlfNTM3MDZfMF8x
```
### API 로드
기본적으로 원본 API 주소로 접속한다면 EUC-KR 방식으로 인코딩되어있어서 한글이 깨져서 출력되며, 
CORS 오류를 우회하기 위해 PHP 프록시를 만들어야 합니다.

1. proxy.php 파일을 다운로드하여, ```$url```에 API 엔드포인트 URL을 삽입합니다.
```php
$url = "http://comci.net:4082/36179?NzM2MjlfNTM3MDZfMF8x";
```
2. index.php 파일을 다운로드하고, ```$apiUrl```에 proxy.php 파일을 링크합니다.

### 응답
```json
{
  "school": "학교명",
  "grade": 1,
  "class": 3,
  "lastModified": "2024-11-04 10:30",
  "startDate": "2024-03-04",
  "schedule": [
    {
      "day": "월요일",
      "dayNumber": 1,
      "periods": [
        {
          "period": 1,
          "periodName": "1교시",
          "subject": "수학",
          "teacher": "홍길동",
          "room": "301",
          "changed": false
        }
      ]
    }
  ]
}
```
