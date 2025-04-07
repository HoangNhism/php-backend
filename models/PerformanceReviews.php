<?php
class PerformanceReviewsModel
{
    private $conn;
    private $table_name = "performance_reviews";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Retrieve all performance reviews.
     */
    public function getAllReviews()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve a performance review by ID.
     */
    public function getReviewById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Add a new performance review.
     */
    public function addReview($data)
    {
        $query = "INSERT INTO " . $this->table_name . " (id, user_id, review_period, score, reviewer_id, comments, created_at) 
                  VALUES (:id, :user_id, :review_period, :score, :reviewer_id, :comments, :created_at)";
        $stmt = $this->conn->prepare($query);

        // Generate random string for id
        $data['id'] = bin2hex(random_bytes(8));

        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':review_period', $data['review_period']);
        $stmt->bindParam(':score', $data['score']);
        $stmt->bindParam(':reviewer_id', $data['reviewer_id']);
        $stmt->bindParam(':comments', $data['comments']);
        $stmt->bindParam(':created_at', $data['created_at']);

        return $stmt->execute();
    }

    /**
     * Update an existing performance review.
     */
    public function updateReview($id, $data)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET user_id = :user_id, review_period = :review_period, score = :score, reviewer_id = :reviewer_id, comments = :comments 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':review_period', $data['review_period']);
        $stmt->bindParam(':score', $data['score']);
        $stmt->bindParam(':reviewer_id', $data['reviewer_id']);
        $stmt->bindParam(':comments', $data['comments']);

        return $stmt->execute();
    }

    /**
     * Delete a performance review.
     */
    public function deleteReview($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>