<?php
class Product {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM products ORDER BY product_id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE product_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function add($name, $price, $category, $quantity, $image = null) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "INSERT INTO products (name, price, category, quantity, image) 
                 VALUES (:name, :price, :category, :quantity, :image)"
            );
            $result = $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':category' => $category,
                ':quantity' => $quantity,
                ':image' => $image
            ]);

            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function update($id, $name, $price, $category, $quantity, $image = null) {
        try {
            $this->conn->beginTransaction();

            if ($image) {
                $stmt = $this->conn->prepare(
                    "UPDATE products SET name=:name, price=:price, category=:category, 
                     quantity=:quantity, image=:image WHERE product_id=:id"
                );
                $result = $stmt->execute([
                    ':id' => $id, ':name' => $name, ':price' => $price,
                    ':category' => $category, ':quantity' => $quantity, ':image' => $image
                ]);
            } else {
                $stmt = $this->conn->prepare(
                    "UPDATE products SET name=:name, price=:price, category=:category, 
                     quantity=:quantity WHERE product_id=:id"
                );
                $result = $stmt->execute([
                    ':id' => $id, ':name' => $name, ':price' => $price,
                    ':category' => $category, ':quantity' => $quantity
                ]);
            }

            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("DELETE FROM products WHERE product_id = :id");
            $result = $stmt->execute([':id' => $id]);

            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function search($keyword) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM products 
             WHERE name LIKE :kw OR category LIKE :kw OR price LIKE :kw 
             ORDER BY product_id DESC"
        );
        $stmt->execute([':kw' => '%' . $keyword . '%']);
        return $stmt->fetchAll();
    }
}
?>