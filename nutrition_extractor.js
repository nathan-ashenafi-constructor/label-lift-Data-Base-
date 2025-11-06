// nutrition_extractor.js - Extract nutrition information from text

/**
 * Parse nutrition information from text lines
 * @param {Array} textLines - Array of text lines from OCR
 * @returns {Object} Nutrition data
 */
function parseNutritionFromText(textLines) {
    // Join all text lines into one string
    const fullText = textLines.join(' ').toLowerCase();
    
    // Initialize nutrition object
    const nutrition = {
        calories: null,
        calories_from_fat: null,
        total_fat: null,
        saturated_fat: null,
        trans_fat: null,
        cholesterol: null,
        sodium: null,
        total_carbohydrate: null,
        dietary_fiber: null,
        sugars: null,
        protein: null,
        vitamin_a: null,
        vitamin_c: null,
        calcium: null,
        iron: null,
        serving_size: null,
        servings_per_container: null
    };
    
    // Helper function to find first match
    const findMatch = (patterns) => {
        for (const pattern of patterns) {
            const match = fullText.match(pattern);
            if (match) return match[1];
        }
        return null;
    };
    
    // Extract calories
    const calValue = findMatch([
        /calories\s*[:\s]*(\d+(?:\.\d+)?)/,
        /(\d+(?:\.\d+)?)\s*cal(?:ories)?/,
        /energy\s*[:\s]*(\d+(?:\.\d+)?)/
    ]);
    if (calValue) nutrition.calories = parseFloat(calValue);
    
    // Extract calories from fat
    const cfatValue = findMatch([
        /calories\s+from\s+fat\s*[:\s]*(\d+(?:\.\d+)?)/,
        /cal\s+from\s+fat\s*[:\s]*(\d+(?:\.\d+)?)/
    ]);
    if (cfatValue) nutrition.calories_from_fat = parseFloat(cfatValue);
    
    // Extract total fat
    const fatValue = findMatch([
        /total\s+fat\s*[:\s]*(\d+(?:\.\d+)?)\s*g/,
        /fat\s*[:\s]*(\d+(?:\.\d+)?)\s*g(?!ram)/
    ]);
    if (fatValue) nutrition.total_fat = parseFloat(fatValue);
    
    // Extract saturated fat
    const satFatValue = findMatch([
        /saturated\s+fat\s*[:\s]*(\d+(?:\.\d+)?)\s*g/,
        /sat\s+fat\s*[:\s]*(\d+(?:\.\d+)?)\s*g/
    ]);
    if (satFatValue) nutrition.saturated_fat = parseFloat(satFatValue);
    
    // Extract trans fat
    const transValue = findMatch([
        /trans\s+fat\s*[:\s]*(\d+(?:\.\d+)?)\s*g/
    ]);
    if (transValue) nutrition.trans_fat = parseFloat(transValue);
    
    // Extract cholesterol
    const cholValue = findMatch([
        /cholesterol\s*[:\s]*(\d+(?:\.\d+)?)\s*mg/
    ]);
    if (cholValue) nutrition.cholesterol = parseFloat(cholValue);
    
    // Extract sodium
    const sodiumValue = findMatch([
        /sodium\s*[:\s]*(\d+(?:\.\d+)?)\s*mg/,
        /salt\s*[:\s]*(\d+(?:\.\d+)?)\s*mg/
    ]);
    if (sodiumValue) nutrition.sodium = parseFloat(sodiumValue);
    
    // Extract total carbohydrates
    const carbValue = findMatch([
        /total\s+carbohydrate\s*[:\s]*(\d+(?:\.\d+)?)\s*g/,
        /carbs?\s*[:\s]*(\d+(?:\.\d+)?)\s*g/
    ]);
    if (carbValue) nutrition.total_carbohydrate = parseFloat(carbValue);
    
    // Extract dietary fiber
    const fiberValue = findMatch([
        /dietary\s+fiber\s*[:\s]*(\d+(?:\.\d+)?)\s*g/,
        /fiber\s*[:\s]*(\d+(?:\.\d+)?)\s*g/
    ]);
    if (fiberValue) nutrition.dietary_fiber = parseFloat(fiberValue);
    
    // Extract sugars
    const sugarValue = findMatch([
        /sugars?\s*[:\s]*(\d+(?:\.\d+)?)\s*g/
    ]);
    if (sugarValue) nutrition.sugars = parseFloat(sugarValue);
    
    // Extract protein
    const proteinValue = findMatch([
        /protein\s*[:\s]*(\d+(?:\.\d+)?)\s*g/
    ]);
    if (proteinValue) nutrition.protein = parseFloat(proteinValue);
    
    // Extract vitamins and minerals
    const vitaValue = findMatch([
        /vitamin\s+a\s*[:\s]*(\d+(?:\.\d+)?)\s*%/
    ]);
    if (vitaValue) nutrition.vitamin_a = parseFloat(vitaValue);
    
    const vitcValue = findMatch([
        /vitamin\s+c\s*[:\s]*(\d+(?:\.\d+)?)\s*%/
    ]);
    if (vitcValue) nutrition.vitamin_c = parseFloat(vitcValue);
    
    const calciumValue = findMatch([
        /calcium\s*[:\s]*(\d+(?:\.\d+)?)\s*%/
    ]);
    if (calciumValue) nutrition.calcium = parseFloat(calciumValue);
    
    const ironValue = findMatch([
        /iron\s*[:\s]*(\d+(?:\.\d+)?)\s*%/
    ]);
    if (ironValue) nutrition.iron = parseFloat(ironValue);
    
    // Extract serving size
    const servingValue = findMatch([
        /serving\s+size\s*[:\s]*(\d+(?:\.\d+)?)\s*(?:g|oz|ml|cup)/,
        /serving\s*[:\s]*(\d+(?:\.\d+)?)/
    ]);
    if (servingValue) nutrition.serving_size = servingValue;
    
    // Extract servings per container
    const servingsValue = findMatch([
        /servings?\s+per\s+container\s*[:\s]*(\d+(?:\.\d+)?)/
    ]);
    if (servingsValue) nutrition.servings_per_container = parseFloat(servingsValue);
    
    // Remove null values
    Object.keys(nutrition).forEach(key => {
        if (nutrition[key] === null) {
            delete nutrition[key];
        }
    });
    
    return nutrition;
}

/**
 * Calculate fitness score (0-100) based on nutrition
 * Higher score = healthier
 * @param {Object} nutrition - Nutrition data
 * @returns {number} Fitness score
 */
function calculateFitnessScore(nutrition) {
    if (!nutrition || Object.keys(nutrition).length === 0) {
        return 0;
    }
    
    let score = 100;
    
    // Penalize high calories
    if (nutrition.calories && nutrition.calories > 400) {
        score -= (nutrition.calories - 400) / 10;
    }
    
    // Penalize high sugar
    if (nutrition.sugars && nutrition.sugars > 10) {
        score -= (nutrition.sugars - 10) * 2;
    }
    
    // Penalize high sodium
    if (nutrition.sodium && nutrition.sodium > 500) {
        score -= (nutrition.sodium - 500) / 100;
    }
    
    // Penalize high saturated fat
    if (nutrition.saturated_fat && nutrition.saturated_fat > 5) {
        score -= (nutrition.saturated_fat - 5) * 3;
    }
    
    // Reward high protein
    if (nutrition.protein && nutrition.protein > 5) {
        score += Math.min(nutrition.protein * 2, 20);
    }
    
    // Reward high fiber
    if (nutrition.dietary_fiber && nutrition.dietary_fiber > 3) {
        score += nutrition.dietary_fiber * 3;
    }
    
    // Clamp score between 0 and 100
    return Math.max(0, Math.min(100, score));
}

/**
 * Format nutrition data for display
 * @param {Object} nutrition - Nutrition data
 * @returns {Object} Formatted nutrition data
 */
function formatNutritionDisplay(nutrition) {
    return {
        raw: nutrition,
        fitness_score: calculateFitnessScore(nutrition),
        summary: {
            calories: nutrition.calories || null,
            protein: nutrition.protein || null,
            carbs: nutrition.total_carbohydrate || null,
            fat: nutrition.total_fat || null,
            fiber: nutrition.dietary_fiber || null,
            sugars: nutrition.sugars || null,
            sodium: nutrition.sodium || null
        }
    };
}