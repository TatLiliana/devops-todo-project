<?php

class TodoController {
    private $db;
    private $conn;

    public function __construct(Database $db) {
        $this->db = $db;
        $this->conn = $db->getConnection();
    }

    public function index() {
        try {
            $sql = "SELECT * FROM todos WHERE 1=1";
            $params = [];

            // Szűrés befejezettség szerint
            if (isset($_GET['completed'])) {
                $completed = filter_var($_GET['completed'], FILTER_VALIDATE_BOOLEAN);
                $sql .= " AND completed = :completed";
                $params[':completed'] = $completed ? 1 : 0;
            }

            // Szűrés prioritás szerint
            if (isset($_GET['priority']) && in_array($_GET['priority'], ['low', 'medium', 'high'])) {
                $sql .= " AND priority = :priority";
                $params[':priority'] = $_GET['priority'];
            }

            $sql .= " ORDER BY created_at DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $todos = $stmt->fetchAll();

            // Boolean értékek konvertálása
            foreach ($todos as &$todo) {
                $todo['completed'] = (bool)$todo['completed'];
            }

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $todos,
                'count' => count($todos)
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function show($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM todos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $todo = $stmt->fetch();

            if (!$todo) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Todo not found'
                ]);
                return;
            }

            $todo['completed'] = (bool)$todo['completed'];

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $todo
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validáció
            if (empty($input['title'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Title is required'
                ]);
                return;
            }

            if (strlen($input['title']) > 200) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Title must not exceed 200 characters'
                ]);
                return;
            }

            $title = $input['title'];
            $description = $input['description'] ?? null;
            $priority = $input['priority'] ?? 'medium';
            $due_date = $input['due_date'] ?? null;

            // Boolean completed mező kezelése (JSON true/false, 1/0, "true"/"false")
            $completed = false;
            if (isset($input['completed'])) {
                if (is_bool($input['completed'])) {
                    $completed = $input['completed'];
                } else {
                    $completed = filter_var($input['completed'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                }
            }

            // Prioritás validálás
            if (!in_array($priority, ['low', 'medium', 'high'])) {
                $priority = 'medium';
            }

            $sql = "INSERT INTO todos (title, description, priority, due_date, completed)
                    VALUES (:title, :description, :priority, :due_date, :completed)";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':priority' => $priority,
                ':due_date' => $due_date,
                ':completed' => $completed ? 1 : 0
            ]);

            $id = $this->conn->lastInsertId();

            // Létrehozott TODO lekérése
            $stmt = $this->conn->prepare("SELECT * FROM todos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $todo = $stmt->fetch();
            $todo['completed'] = (bool)$todo['completed'];

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Todo created successfully',
                'data' => $todo
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function update($id) {
        try {
            // TODO létezésének ellenőrzése
            $stmt = $this->conn->prepare("SELECT id FROM todos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Todo not found'
                ]);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $updates = [];
            $params = [':id' => $id];

            if (isset($input['title'])) {
                if (strlen($input['title']) > 200) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Title must not exceed 200 characters'
                    ]);
                    return;
                }
                $updates[] = "title = :title";
                $params[':title'] = $input['title'];
            }

            if (isset($input['description'])) {
                $updates[] = "description = :description";
                $params[':description'] = $input['description'];
            }

            if (isset($input['priority']) && in_array($input['priority'], ['low', 'medium', 'high'])) {
                $updates[] = "priority = :priority";
                $params[':priority'] = $input['priority'];
            }

            if (isset($input['due_date'])) {
                $updates[] = "due_date = :due_date";
                $params[':due_date'] = $input['due_date'];
            }

            if (isset($input['completed'])) {
                $updates[] = "completed = :completed";
                // Boolean értékek helyes kezelése (JSON true/false, 1/0, "true"/"false")
                if (is_bool($input['completed'])) {
                    $params[':completed'] = $input['completed'] ? 1 : 0;
                } else {
                    $params[':completed'] = filter_var($input['completed'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 1 : 0;
                }
            }

            if (empty($updates)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'No fields to update'
                ]);
                return;
            }

            $sql = "UPDATE todos SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            // Frissített TODO lekérése
            $stmt = $this->conn->prepare("SELECT * FROM todos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $todo = $stmt->fetch();
            $todo['completed'] = (bool)$todo['completed'];

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Todo updated successfully',
                'data' => $todo
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function toggle($id) {
        try {
            $stmt = $this->conn->prepare("SELECT completed FROM todos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $todo = $stmt->fetch();

            if (!$todo) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Todo not found'
                ]);
                return;
            }

            $newStatus = !$todo['completed'];

            $stmt = $this->conn->prepare("UPDATE todos SET completed = :completed WHERE id = :id");
            $stmt->execute([
                ':completed' => $newStatus ? 1 : 0,
                ':id' => $id
            ]);

            // Frissített TODO lekérése
            $stmt = $this->conn->prepare("SELECT * FROM todos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $todo = $stmt->fetch();
            $todo['completed'] = (bool)$todo['completed'];

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Todo completion toggled',
                'data' => $todo
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM todos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Todo not found'
                ]);
                return;
            }

            $stmt = $this->conn->prepare("DELETE FROM todos WHERE id = :id");
            $stmt->execute([':id' => $id]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Todo deleted successfully'
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}
