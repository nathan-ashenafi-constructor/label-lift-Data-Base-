from flask import Flask, send_from_directory, request, redirect, url_for
import os
import mysql.connector
from mysql.connector import Error
from datetime import datetime

# Base folder where your HTML files are
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
app = Flask(__name__)

# ==================== Database Configuration ====================
db_config = {
    'host': 'clabsql',
    'user': 'mznaien',
    'password': 'YDrknvGJINrcbATt',
    'database': 'db_mznaien'
}

def get_db_connection():
    """Create and return a database connection"""
    try:
        connection = mysql.connector.connect(**db_config)
        return connection
    except Error as e:
        print(f"Error connecting to database: {e}")
        return None

def execute_query(query, params=None, fetch=False):
    """Execute a query and optionally fetch results"""
    connection = get_db_connection()
    if not connection:
        return None
    try:
        cursor = connection.cursor(dictionary=True)
        if params:
            cursor.execute(query, params)
        else:
            cursor.execute(query)
        if fetch:
            result = cursor.fetchall()
        else:
            connection.commit()
            result = cursor.lastrowid
        cursor.close()
        connection.close()
        return result
    except Error as e:
        print(f"Query error: {e}")
        return None

# ==================== Serve HTML Files ====================
@app.route('/')
def home():
    return send_from_directory(BASE_DIR, 'index.html')

@app.route('/<path:filename>')
def serve_file(filename):
    """Serve any HTML/CSS/JS file directly from src/"""
    try:
        return send_from_directory(BASE_DIR, filename)
    except:
        return "File not found", 404

# ==================== Products ====================
@app.route('/products/add', methods=['GET', 'POST'])
def add_product():
    if request.method == 'POST':
        name = request.form.get('name')
        barcode = request.form.get('barcode')
        size_of_serving = request.form.get('size_of_serving')
        category_id = request.form.get('category_id')
        brand_id = request.form.get('brand_id')
        if all([name, barcode, category_id, brand_id]):
            execute_query("INSERT INTO Products (name, barcode, size_of_serving, category_id, brand_id) VALUES (%s, %s, %s, %s, %s)",
                          (name, barcode, size_of_serving, category_id, brand_id))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "Required fields missing", 400
    return send_from_directory(BASE_DIR, 'products_input.html')

# ==================== Brands ====================
@app.route('/brands/add', methods=['GET', 'POST'])
def add_brand():
    if request.method == 'POST':
        brand_name = request.form.get('brand_name')
        manufacturer = request.form.get('manufacturer')
        if brand_name and manufacturer:
            execute_query("INSERT INTO Brands (brand_name, manufacturer) VALUES (%s, %s)", 
                         (brand_name, manufacturer))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "Brand name and manufacturer required", 400
    return send_from_directory(BASE_DIR, 'brands_input.html')

# ==================== Categories ====================
@app.route('/categories/add', methods=['GET', 'POST'])
def add_category():
    if request.method == 'POST':
        category_name = request.form.get('category_name')
        health_standing = request.form.get('health_standing')
        if category_name and health_standing:
            execute_query("INSERT INTO Categories (category_name, health_standing) VALUES (%s, %s)", 
                         (category_name, health_standing))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "Category name and health standing required", 400
    return send_from_directory(BASE_DIR, 'categories_input.html')

# ==================== Tags ====================
@app.route('/tags/add', methods=['GET', 'POST'])
def add_tag():
    if request.method == 'POST':
        tag_name = request.form.get('tag_name')
        tag_type = request.form.get('tag_type')
        if tag_name and tag_type:
            execute_query("INSERT INTO Tags (tag_name, tag_type) VALUES (%s, %s)", 
                         (tag_name, tag_type))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "Tag name and type required", 400
    return send_from_directory(BASE_DIR, 'tags_input.html')

# ==================== Nutrition Facts ====================
@app.route('/nutrition/add', methods=['GET', 'POST'])
def add_nutrition():
    if request.method == 'POST':
        product_id = request.form.get('product_id')
        calorie = request.form.get('calorie')
        fats = request.form.get('fats')
        sodium = request.form.get('sodium')
        sugars = request.form.get('sugars')
        protein = request.form.get('protein')
        dietary_fiber = request.form.get('dietary_fiber')
        fitness_score = request.form.get('fitness_score')
        dietary_restriction = request.form.get('dietary_restriction')
        if product_id and calorie:
            execute_query("""INSERT INTO NutritionFacts 
                             (product_id, calorie, fats, sodium, sugars, protein, dietary_fiber, fitness_score, dietary_restriction)
                             VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)""",
                          (product_id, calorie, fats, sodium, sugars, protein, dietary_fiber, fitness_score, dietary_restriction))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "Product ID and calorie required", 400
    return send_from_directory(BASE_DIR, 'nutrition_input.html')

# ==================== Scans ====================
@app.route('/scans/add', methods=['GET', 'POST'])
def add_scan():
    if request.method == 'POST':
        user_id = request.form.get('user_id')
        product_id = request.form.get('product_id')
        if user_id and product_id:
            execute_query("INSERT INTO Scans (user_id, product_id, scan_date) VALUES (%s, %s, %s)",
                          (user_id, product_id, datetime.now()))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "User ID and Product ID required", 400
    return send_from_directory(BASE_DIR, 'scans_input.html')

# ==================== Product Alternatives ====================
@app.route('/alternatives/add', methods=['GET', 'POST'])
def add_alternative():
    if request.method == 'POST':
        product_id = request.form.get('product_id')
        alternative_product_id = request.form.get('alternative_product_id')
        if product_id and alternative_product_id and product_id != alternative_product_id:
            execute_query("INSERT INTO ProductAlternatives (product_id, alternative_product_id) VALUES (%s, %s)",
                          (product_id, alternative_product_id))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "Valid product IDs required", 400
    return send_from_directory(BASE_DIR, 'alternatives_input.html')

# ==================== Product Tags ====================
@app.route('/product-tags/add', methods=['GET', 'POST'])
def add_product_tag():
    if request.method == 'POST':
        product_id = request.form.get('product_id')
        tag_id = request.form.get('tag_id')
        if product_id and tag_id:
            execute_query("INSERT INTO ProductTags (product_id, tag_id) VALUES (%s, %s)", 
                         (product_id, tag_id))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "Product ID and Tag ID required", 400
    return send_from_directory(BASE_DIR, 'product_tags_input.html')

# ==================== Fitness User ====================
@app.route('/fitness-user/add', methods=['GET', 'POST'])
def add_fitness_user():
    if request.method == 'POST':
        user_id = request.form.get('user_id')
        protein_goal = request.form.get('protein_goal')
        user_check = execute_query("SELECT user_id FROM Users WHERE user_id=%s", (user_id,), fetch=True)
        if user_check:
            execute_query("INSERT INTO FitnessUser (user_id, protein_goal) VALUES (%s, %s)", 
                         (user_id, protein_goal))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "User not found", 404
    return send_from_directory(BASE_DIR, 'fitness_user_input.html')

# ==================== Casual User ====================
@app.route('/casual-user/add', methods=['GET', 'POST'])
def add_casual_user():
    if request.method == 'POST':
        user_id = request.form.get('user_id')
        usage_frequency = request.form.get('usage_frequency')
        user_check = execute_query("SELECT user_id FROM Users WHERE user_id=%s", (user_id,), fetch=True)
        if user_check:
            execute_query("INSERT INTO CasualUser (user_id, usage_frequency) VALUES (%s, %s)", 
                         (user_id, usage_frequency))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "User not found", 404
    return send_from_directory(BASE_DIR, 'casual_user_input.html')

# ==================== Health Conscious User ====================
@app.route('/health-user/add', methods=['GET', 'POST'])
def add_health_user():
    if request.method == 'POST':
        user_id = request.form.get('user_id')
        health_focus = request.form.get('health_focus')
        user_check = execute_query("SELECT user_id FROM Users WHERE user_id=%s", (user_id,), fetch=True)
        if user_check:
            execute_query("INSERT INTO HealthConsciousUser (user_id, health_focus) VALUES (%s, %s)", 
                         (user_id, health_focus))
            return send_from_directory(BASE_DIR, 'feedback.html')
        return "User not found", 404
    return send_from_directory(BASE_DIR, 'health_user_input.html')

if __name__ == '__main__':
    app.run(debug=True, port=5000)