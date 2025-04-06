<?php
require_once __DIR__ . '/../models/PerformanceReviews.php';
require_once __DIR__ . '/../config/database.php';
class PRController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new PerformanceReviewsModel($db);
    }

    /**
     * Get all performance reviews.
     */
    public function getAllReviews()
    {
        try {
            $reviews = $this->model->getAllReviews();
            echo json_encode(['success' => true, 'data' => $reviews]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get a performance review by ID.
     */
    public function getReviewById($id)
    {
        try {
            $review = $this->model->getReviewById($id);
            if ($review) {
                echo json_encode(['success' => true, 'data' => $review]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Review not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Add a new performance review.
     */
    public function addReview($data)
    {
        try {
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->model->addReview($data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Review added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add review']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Update an existing performance review.
     */
    public function updateReview($id, $data)
    {
        try {
            $result = $this->model->updateReview($id, $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update review']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete a performance review.
     */
    public function deleteReview($id)
    {
        try {
            $result = $this->model->deleteReview($id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Review deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete review']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

// Example usage:
// Assuming you have a database connection $db
// $controller = new PRController($db);
// $controller->getAllReviews();
?>