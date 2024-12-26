<?php
require_once 'filemaker_helper.php';  // or use include_once if you prefer

// Now you can use the FilemakerAuth class
try {
    $auth = new FilemakerAuth('https://your-host', 'your-database-name', 'your-username', 'your-password');

    // Fetch example
    $records = $auth->fetch('YourLayoutName', ['FieldName' => 'Value']);
    if (isset($records["error"])) {
        echo "Error Fetching: " . $records["error"];
    } else {
        print_r($records);
    }

    // Insert example
    $fieldsData = ['Field1' => 'Value1', 'Field2' => 'Value2'];
    $insertResult = $auth->insert('YourLayoutName', $fieldsData);
    if (isset($insertResult["error"])) {
        echo "Error Inserting: " . $insertResult["error"];
    } else {
        echo "Record Inserted Successfully!";
        print_r($insertResult);
    }

    // Update example
    $recordId = 12345;
    $updateFields = ['Field1' => 'NewValue1', 'Field2' => 'NewValue2'];
    $updateResult = $auth->update('YourLayoutName', $recordId, $updateFields);
    if (isset($updateResult["error"])) {
        echo "Error Updating: " . $updateResult["error"];
    } else {
        echo "Record Updated Successfully!";
        print_r($updateResult);
    }

    // Delete example
    $deleteResult = $auth->delete('YourLayoutName', 12345);
    if (isset($deleteResult["error"])) {
        echo "Error Deleting: " . $deleteResult["error"];
    } else {
        echo "Record Deleted Successfully!";
    }

} catch (Exception $e) {
    echo "Initialization Error: " . $e->getMessage();
}
