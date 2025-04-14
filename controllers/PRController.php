<?php
require_once __DIR__ . '/../models/PerformanceReviews.php';
require_once __DIR__ . '/../config/database.php';

class PrController {
    private $database;
    private $db;
    private $pr;

    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->pr = new PerformanceReviewsModel($this->db);
    }

    public function createPr($data) {
        try {
            if (!isset($data['user_id']) || !isset($data['score']) || !isset($data['reviewer_id'])) {
                throw new Exception("Missing required fields");
            }

            $result = $this->pr->create($data);
            return array(
                "success" => true,
                "message" => "Performance review created successfully",
                "data" => $result
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to create performance review",
                "error" => $e->getMessage()
            );
        }
    }

    public function getPr() {
        try {
            $result = $this->pr->getAll();
            return array(
                "success" => true,
                "data" => $result
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to get performance reviews",
                "error" => $e->getMessage()
            );
        }
    }

    public function getPrById($id) {
        try {
            $result = $this->pr->getById($id);
            return array(
                "success" => true,
                "data" => $result
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to get performance review",
                "error" => $e->getMessage()
            );
        }
    }

    public function getPrByUserId($user_id) {
        try {
            $result = $this->pr->getByUserId($user_id);
            return array(
                "success" => true,
                "data" => $result
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to get performance reviews",
                "error" => $e->getMessage()
            );
        }
    }

    public function getPrByReviewerId($reviewer_id) {
        try {
            $result = $this->pr->getByReviewerId($reviewer_id);
            return array(
                "success" => true,
                "data" => $result
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to get performance reviews",
                "error" => $e->getMessage()
            );
        }
    }

    public function updatePr($id, $data) {
        try {
            $result = $this->pr->update($id, $data);
            return array(
                "success" => true,
                "message" => "Performance review updated successfully",
                "data" => $result
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to update performance review",
                "error" => $e->getMessage()
            );
        }
    }

    public function deletePr($id) {
        try {
            $this->pr->delete($id);
            return array(
                "success" => true,
                "message" => "Performance review deleted successfully"
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to delete performance review",
                "error" => $e->getMessage()
            );
        }
    }

    public function getMonthlyStats($year, $month) {
        try {
            $result = $this->pr->getMonthlyStats($year, $month);
            return array(
                "success" => true,
                "data" => $result
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to get monthly stats",
                "error" => $e->getMessage()
            );
        }
    }

    public function getQuarterlyStats($year, $quarter) {
        try {
            $result = $this->pr->getQuarterlyStats($year, $quarter);
            return array(
                "success" => true,
                "data" => $result
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to get quarterly stats",
                "error" => $e->getMessage()
            );
        }
    }

    public function getYearlyStats($year) {
        try {
            $result = $this->pr->getYearlyStats($year);
            return array(
                "success" => true,
                "data" => $result
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Failed to get yearly stats",
                "error" => $e->getMessage()
            );
        }
    }
}