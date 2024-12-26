<?php

// from feature branch
class FilemakerAuth {
    private $host;
    private $dbName;
    private $username;
    private $password;
    private $token;

    public function __construct($host, $dbName, $username, $password) {
        $this->host = $host;
        $this->dbName = $dbName;
        $this->username = $username;
        $this->password = $password;
        $this->token = $this->getSessionToken();
    }

    private function getSessionToken() {
        $url = "{$this->host}/fmi/data/vLatest/databases/{$this->dbName}/sessions";
        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode("{$this->username}:{$this->password}")
        ];

        $body = json_encode([
            'fmDataSource' => [
                [
                    'database' => $this->dbName,
                    'username' => $this->username,
                    'password' => $this->password,
                ]
            ]
        ]);

        // Initialize cURL to get session token
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            die("Failed to authenticate. HTTP Code: $httpCode. Response: $response");
        }

        $responseData = json_decode($response, true);
        if (isset($responseData['response']['token'])) {
            return $responseData['response']['token'];
        }

        die("Failed to authenticate. No token found. Response: $response");
    }

    private function makeCurlRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        
        // Add the session token to the Authorization header
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("CURL Error: $error");
        }

        curl_close($ch);
        return ['httpCode' => $httpCode, 'response' => json_decode($response, true)];
    }

    public function fetch($layoutName) {
        try {
            $url = "{$this->host}/fmi/data/vLatest/databases/{$this->dbName}/layouts/{$layoutName}/records";
            $result = $this->makeCurlRequest($url, 'GET');
            if ($result['httpCode'] !== 200) {
                throw new Exception("Fetch Error: " . json_encode($result['response']));
            }
            return $result['response']['response']['data'] ?? [];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function insert($layoutName, $fieldsData) {
        try {
            $url = "{$this->host}/fmi/data/vLatest/databases/{$this->dbName}/layouts/{$layoutName}/records";
            $data = ["fieldData" => $fieldsData];

            $result = $this->makeCurlRequest($url, 'POST', $data);
            if ($result['httpCode'] !== 200) {
                throw new Exception("Insert Error: " . json_encode($result['response']));
            }

            return $result['response']['response']['data'] ?? [];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function update($layoutName, $recordId, $fieldsData) {
        try {
            $url = "{$this->host}/fmi/data/vLatest/databases/{$this->dbName}/layouts/{$layoutName}/records/{$recordId}";
            $data = ["fieldData" => $fieldsData];

            $result = $this->makeCurlRequest($url, 'PATCH', $data);
            if ($result['httpCode'] !== 200) {
                throw new Exception("Update Error: " . json_encode($result['response']));
            }

            return $result['response']['response']['data'] ?? [];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function delete($layoutName, $recordId) {
        try {
            $url = "{$this->host}/fmi/data/vLatest/databases/{$this->dbName}/layouts/{$layoutName}/records/{$recordId}";
            $result = $this->makeCurlRequest($url, 'DELETE');
            if ($result['httpCode'] !== 200) {
                throw new Exception("Delete Error: " . json_encode($result['response']));
            }
            return ["success" => true];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }
}
?>