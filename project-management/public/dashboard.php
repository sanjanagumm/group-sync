<?php
include '../config/db_connection.php';

// Fetch project details (static text for now)
$projectDetails = "Project details: Lorem ipsum";

// Fetch free students (group_id is NULL)
$stmt = $conn->prepare("SELECT * FROM students WHERE group_id IS NULL");
$stmt->execute();
$freeStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available topics (is_selected is false)
$stmt = $conn->prepare("SELECT * FROM topics WHERE is_selected = 0");
$stmt->execute();
$availableTopics = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle group creation
    $groupName = $_POST['group_name'];
    $rollNumbers = $_POST['roll_numbers']; // Expecting an array
    $topicId = $_POST['topic_id'];

    // Create the group and update assignments
    if (count($rollNumbers) === 3 && !in_array("", $rollNumbers)) {
        // Insert new group
        $stmt = $conn->prepare("INSERT INTO `groups` (group_name) VALUES (?)");
        $stmt->bindValue(1, $groupName);
        $stmt->execute();
        $groupId = $conn->lastInsertId();

        // Update students with new group_id
        foreach ($rollNumbers as $rollNumber) {
            // Update student with new group_id
            $stmt = $conn->prepare("UPDATE students SET group_id = ? WHERE student_id = ?");
            $stmt->bindValue(1, $groupId);
            $stmt->bindValue(2, $rollNumber);
            $stmt->execute();
        }

        // Create assignment
        $stmt = $conn->prepare("INSERT INTO assignments (group_id, topic_id) VALUES (?, ?)");
        $stmt->bindValue(1, $groupId);
        $stmt->bindValue(2, $topicId);
        $stmt->execute();

        // Update the selected topic
        $stmt = $conn->prepare("UPDATE topics SET is_selected = 1 WHERE topic_id = ?");
        $stmt->bindValue(1, $topicId);
        $stmt->execute();

        echo "<p style='color: green;'>Group '$groupName' created successfully!</p>";
    } else {
        echo "<p style='color: red;'>Please enter exactly 3 roll numbers.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .box {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            flex: 1;
            min-width: 300px; /* Minimum width for responsiveness */
        }

        .logo {
            text-align: center;
            font-size: 18px; /* Smaller font size */
            font-style: italic; /* Italics */
            color: #e67e22; /* Change color to orange */
            font-family: 'Georgia', serif; /* Change font */
        }

        h1, h2, h3 {
            color: #2c3e50;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            background: #f8d7da; /* Light red background */
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #e67e22; /* Change color to orange */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        button:hover {
            background-color: #d35400; /* Darker orange on hover */
        }

        .success-message {
            color: green;
        }

        .error-message {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box logo">
            <h1>GroupSync</h1>
        </div>

        <div class="box">
            <h2>Project Details</h2>
            <p><?php echo $projectDetails; ?></p>
        </div>

        <div class="box">
            <h2>Available Topics</h2>
            <ul>
                <?php foreach ($availableTopics as $topic): ?>
                    <li><?php echo htmlspecialchars($topic['topic_name']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="box">
            <h2>Free Students</h2>
            <ul>
                <?php foreach ($freeStudents as $student): ?>
                    <li><?php echo htmlspecialchars($student['student_id'] . " - " . $student['name']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="box">
            <h2>Create Group</h2>
            <form method="POST">
                <label for="group_name">Group Name:</label>
                <input type="text" name="group_name" required>

                <label for="roll_numbers">Roll Numbers:</label>
                <input type="text" name="roll_numbers[]" placeholder="Roll Number 1" required>
                <input type="text" name="roll_numbers[]" placeholder="Roll Number 2" required>
                <input type="text" name="roll_numbers[]" placeholder="Roll Number 3" required>

                <label for="topic_id">Select Topic:</label>
                <select name="topic_id" required>
                    <?php foreach ($availableTopics as $topic): ?>
                        <option value="<?php echo htmlspecialchars($topic['topic_id']); ?>"><?php echo htmlspecialchars($topic['topic_name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Create Group</button>
            </form>
        </div>
    </div>
</body>
</html>
