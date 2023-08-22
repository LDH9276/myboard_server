<?php
class JWT {
    protected $alg;
    protected $access_secret_key;
    protected $refresh_secret_key;

    // 생성자
    function __construct() {
        // 사용할 알고리즘
        $this->alg = 'sha256';
        // 액세스 토큰 비밀 키
        $this->access_secret_key = getenv('JWT_ACCESS_KEY');
        // 리프레시 토큰 비밀 키
        $this->refresh_secret_key = getenv('JWT_REFRESH_KEY');
    }

    // 액세스 토큰 비밀 키 가져오기
    public function getAccessSecretKey() {
        return $this->access_secret_key;
    }

    // 리프레시 토큰 비밀 키 가져오기
    public function getRefreshSecretKey() {
        return $this->refresh_secret_key;
    }

    // 액세스 토큰 발급하기
    function issueAccessToken(array $data_token): string {
        // 헤더 - 사용할 알고리즘과 타입 명시
        $header = json_encode(array('alg' => $this->alg, 'typ' => 'JWT'));
        // 페이로드 - 전달할 데이터
        $payload = json_encode($data_token, JSON_UNESCAPED_UNICODE);
        // 시그니처
        $signature = hash($this->alg, $header . $payload . $this->access_secret_key);
        return base64_encode($header . '.' . $payload . '.' . $signature);
    }

    // 리프레시 토큰 발급하기
    function issueRefreshToken(array $data_token): string {
        // 헤더 - 사용할 알고리즘과 타입 명시
        $header = json_encode(array('alg' => $this->alg, 'typ' => 'JWT'));
        // 페이로드 - 전달할 데이터
        $payload = json_encode($data_token, JSON_UNESCAPED_UNICODE);
        // 시그니처
        $signature = hash($this->alg, $header . $payload . $this->refresh_secret_key);
        return base64_encode($header . '.' . $payload . '.' . $signature);
    }

    // 토큰 해석하기
    function decodeToken($token, $secret_key) {

        // 구분자 . 로 토큰 나누기
        $parted = explode('.', base64_decode($token));
        $signature = $parted[2] ?? null;

        // $parted 배열의 크기 확인
        if (count($parted) < 3) {
            return "invalid token.";
        }

        // 토큰 만들 때처럼 시그니처 생성 후 비교
        if (hash($this->alg, $parted[0] . $parted[1] . $secret_key) != $signature) {
            return "signature error.";
        }
      // 만료 검사
        $payload = json_decode($parted[1], true);
            if ($payload['exp'] < time()) {
            return "토큰이 만료되었습니다.";
        }
        return $payload;
    }

    // 액세스 토큰 해석하기
    function decodeAccessToken($token) {
        return $this->decodeToken($token, $this->access_secret_key);
    }

    // 리프레시 토큰 해석하기
    function decodeRefreshToken($token) {
        return $this->decodeToken($token, $this->refresh_secret_key);
    }
}
?>