import pandas as pd
import numpy as np
from sklearn.metrics import accuracy_score, precision_score, recall_score, f1_score
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report
import xgboost as xgb
import shap
from flask import Flask, jsonify, request, render_template
import mysql.connector
from flask_cors import CORS
import traceback
from decimal import Decimal
from datetime import datetime
import json



app = Flask(__name__)
CORS(app)

db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'emp_performance_db'
}

class SafeJSONEncoder(json.JSONEncoder):
    def default(self, obj):
        if isinstance(obj, (np.generic, Decimal)):
            return float(obj)
        if isinstance(obj, (np.ndarray)):
            return obj.tolist()
        return super().default(obj)

def get_categories():
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(dictionary=True)
    
    cursor.execute("SELECT category, weight, scale, score, rating FROM evaluation_criteria")
    evaluation_criteria = cursor.fetchall()
    
    cursor.execute("SELECT rate, min_instances, max_instances, min_minutes, max_minutes, min_absenteeism, max_absenteeism, min_uab_uhd, max_uab_uhd FROM tardiness_rating")
    tardiness_rating = cursor.fetchall()
    
    cursor.execute("SELECT min_minor, max_minor, min_grave, max_grave, min_suspension, max_suspension, rate FROM discipline_grave")
    discipline_rating = cursor.fetchall()
    
    cursor.execute("SELECT min_score, max_score, rating FROM performance_rating_scale")
    performance_rating = cursor.fetchall()
    
    cursor.close()
    conn.close()
    
    return {
        'evaluation_criteria': evaluation_criteria,
        'tardiness_rating': tardiness_rating,
        'discipline_rating': discipline_rating,
        'performance_rating': performance_rating
    }

def get_employee_data():
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(dictionary=True)
    
    query = """
    SELECT td.emp_id, td.emp_name, td.age, td.gender, td.emp_status, td.department, 
           td.start_date, td.regularization, td.tenure, ta.eval_id, ta.tardiness, ta.tardy, 
           ta.comb_ab_hd, ta.comb_uab_uhd, ta.AB, ta.UAB, ta.HD, ta.UHD, tbd.minor, 
           tbd.grave, tbd.suspension, tbo.performance, tbo.manager_input, tbo.psa_input, 
           tbe.highlight, tbe.lowlight, tbe.administration, tbe.knowledge_of_work, 
           tbe.quality_of_work, tbe.communication, tbe.team, tbe.decision, tbe.dependability, 
           tbe.adaptability, tbe.leadership, tbe.customer, tbe.human_relations, 
           tbe.personal_appearance, tbe.safety, tbe.discipline, tbe.potential_growth, 
           tp.position_name, dp.dept_name  
    FROM tbl_employee_details td 
    INNER JOIN tbl_eval_attendance ta ON ta.emp_id = td.emp_id 
    INNER JOIN tbl_eval_discipline tbd ON tbd.eval_id = ta.eval_id 
    INNER JOIN tbl_eval_others tbo ON tbo.eval_id = ta.eval_id 
    INNER JOIN tbl_evaluation tbe ON tbe.emp_id = td.emp_id
    INNER JOIN tbl_positions tp ON tp.position_id = td.position
    INNER JOIN tbl_department dp ON dp.dept_id = td.department
    WHERE td.active_status = 1
    ORDER BY td.emp_name ASC
    """
    
    cursor.execute(query)
    results = cursor.fetchall()
    
    cursor.close()
    conn.close()
    
    return results

def get_promotion_history():
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(dictionary=True)
    
    query = """
    SELECT ph.promotion_id, ph.emp_id, ph.promotion_date, ph.reason, ph.performance_rating,
           prev_pos.position_name as previous_position, new_pos.position_name as new_position,
           prev_dept.dept_name as previous_department, new_dept.dept_name as new_department
    FROM tbl_promotion_history ph
    LEFT JOIN tbl_positions prev_pos ON prev_pos.position_id = ph.previous_position_id
    LEFT JOIN tbl_positions new_pos ON new_pos.position_id = ph.new_position_id
    LEFT JOIN tbl_department prev_dept ON prev_dept.dept_id = ph.previous_department_id
    LEFT JOIN tbl_department new_dept ON new_dept.dept_id = ph.new_department_id
    ORDER BY ph.promotion_date DESC
    """
    
    cursor.execute(query)
    results = cursor.fetchall()
    
    cursor.close()
    conn.close()
    
    return results

def map_manager_score(input_score):
    """Convert manager input (1-5 scale) to 0-10 scale"""
    if input_score is None or pd.isna(input_score):
        return 0
    return min(10, max(0, round(float(input_score) * 2)))

def calculate_performance_score(performance, performance_rating):
    """Calculate performance score based on performance_rating_scale table"""
    if performance is None or pd.isna(performance):
        return 0
        
    performance = float(performance)
    for rating in performance_rating:
        min_score = float(rating['min_score']) if rating['min_score'] is not None else -float('inf')
        max_score = float(rating['max_score']) if rating['max_score'] is not None else float('inf')
        
        if min_score <= performance <= max_score:
            return float(rating['rating'])
    return 0.0

def calculate_attendance_score(row, tardiness_rating):
    """Calculate attendance score based on tardiness_rating table"""
    tardiness = float(row['tardiness']) if not pd.isna(row['tardiness']) else 0.0
    comb_ab_hd = float(row['comb_ab_hd']) if not pd.isna(row['comb_ab_hd']) else 0.0
    comb_uab_uhd = float(row['comb_uab_uhd']) if not pd.isna(row['comb_uab_uhd']) else 0.0
    
    worst_rate = 10 
    
    for rating in tardiness_rating:
        rate = float(rating['rate'])
        min_inst = float(rating['min_instances']) if rating['min_instances'] is not None else -float('inf')
        max_inst = float(rating['max_instances']) if rating['max_instances'] is not None else float('inf')
        min_abs = float(rating['min_absenteeism']) if rating['min_absenteeism'] is not None else -float('inf')
        max_abs = float(rating['max_absenteeism']) if rating['max_absenteeism'] is not None else float('inf')
        min_uab = float(rating['min_uab_uhd']) if rating['min_uab_uhd'] is not None else -float('inf')
        max_uab = float(rating['max_uab_uhd']) if rating['max_uab_uhd'] is not None else float('inf')
        
        if (min_inst <= tardiness <= max_inst) or (min_abs <= comb_ab_hd <= max_abs) or (min_uab <= comb_uab_uhd <= max_uab):
            if rate < worst_rate:
                worst_rate = rate
    
    return worst_rate

def calculate_discipline_score(row, discipline_rating):
    """Calculate discipline score based on discipline_grave table"""
    minor = float(row['minor']) if not pd.isna(row['minor']) else 0.0
    grave = float(row['grave']) if not pd.isna(row['grave']) else 0.0
    suspension = float(row['suspension']) if not pd.isna(row['suspension']) else 0.0
    
    worst_rate = 10  # Start with best possible score
    
    for rating in discipline_rating:
        rate = float(rating['rate'])
        min_minor = float(rating['min_minor']) if rating['min_minor'] is not None else -float('inf')
        max_minor = float(rating['max_minor']) if rating['max_minor'] is not None else float('inf')
        min_grave = float(rating['min_grave']) if rating['min_grave'] is not None else -float('inf')
        max_grave = float(rating['max_grave']) if rating['max_grave'] is not None else float('inf')
        min_susp = float(rating['min_suspension']) if rating['min_suspension'] is not None else -float('inf')
        max_susp = float(rating['max_suspension']) if rating['max_suspension'] is not None else float('inf')
        
        if (min_minor <= minor <= max_minor) or (min_grave <= grave <= max_grave) or (min_susp <= suspension <= max_susp):
            if rate < worst_rate:
                worst_rate = rate
    
    return worst_rate


def preprocess_data(employee_data, categories, promotion_history):
    try:

        df = pd.DataFrame(employee_data)
        promo_df = pd.DataFrame(promotion_history)


        df['tenure_years'] = df['tenure'].apply(lambda x: float(x.split(' ')[0])) if 'tenure' in df.columns else 0
        

        if 'age' in df.columns:
            df['age_group'] = pd.cut(df['age'],
                               bins=[0, 25, 35, 45, 55, 100],
                               labels=['<25', '26-35', '36-45', '46-55', '55+'])
        

        if 'gender' in df.columns:
            df['gender'] = df['gender'].str.upper().str.strip()
        

        evaluation_criteria = categories['evaluation_criteria']
        tardiness_rating = categories['tardiness_rating']
        discipline_rating = categories['discipline_rating']
        performance_rating = categories['performance_rating']
        

        df = df.assign(
            manager_input=df['manager_input'].fillna(0),
            psa_input=df['psa_input'].fillna('NU')
        )
        

        df['psa_input'] = df['psa_input'].apply(lambda x: 1 if x == 'NU' else 0)
        df['emp_status'] = df['emp_status'].apply(lambda x: 1 if x == 'REGULAR' else 0)
        

        df['attendance_score'] = df.apply(lambda x: calculate_attendance_score(x, tardiness_rating), axis=1)
        df['discipline_score'] = df.apply(lambda x: calculate_discipline_score(x, discipline_rating), axis=1)
        df['performance_score'] = df['performance'].apply(lambda x: calculate_performance_score(x, performance_rating))
        df['manager_score'] = df['manager_input'].apply(map_manager_score)
        df['psa_score'] = df['psa_input'] * 10
        

        weights = {criteria['category']: float(criteria['weight'])/100 for criteria in evaluation_criteria}
        df['total_score'] = (
            df['attendance_score'] * weights['ATTENDANCE'] +
            df['discipline_score'] * weights['DISCIPLINE'] +
            df['performance_score'] * weights['PERFORMANCE EVAL'] +
            df['manager_score'] * weights['MNGR INPUT'] +
            df['psa_score'] * weights['PSA INPUT']
        )
        
        if not promo_df.empty:
            promo_counts = promo_df['emp_id'].value_counts().reset_index()
            promo_counts.columns = ['emp_id', 'promotion_count']
            
            promo_df['promotion_date'] = pd.to_datetime(promo_df['promotion_date'])
            recent_promo = promo_df.sort_values('promotion_date').groupby('emp_id').last().reset_index()
            
            df = pd.merge(df, promo_counts, on='emp_id', how='left')
            df = pd.merge(df, recent_promo[['emp_id', 'promotion_date', 'new_position']], 
                          on='emp_id', how='left', suffixes=('', '_recent'))
            
            df['promotion_count'] = df['promotion_count'].fillna(0)
            df['has_been_promoted'] = (df['promotion_count'] > 0).astype(int)
            
            df['days_since_promotion'] = (datetime.now() - df['promotion_date']).dt.days
        else:
            df['promotion_count'] = 0
            df['has_been_promoted'] = 0
            df['days_since_promotion'] = np.nan
        

        if not promo_df.empty:
           
            promo_employees = set(promo_df['emp_id'])
            df['promotion_flag'] = df['emp_id'].apply(lambda x: 1 if x in promo_employees else 0)
            
            
            threshold = df[df['promotion_flag'] == 0]['total_score'].quantile(0.8)
            df.loc[df['promotion_flag'] == 0, 'promotion_flag'] = (
                df[df['promotion_flag'] == 0]['total_score'] >= threshold
            ).astype(int)
        else:
            threshold = df['total_score'].quantile(0.8)
            df['promotion_flag'] = (df['total_score'] >= threshold).astype(int)
        
        return df
                
    except Exception as e:
        app.logger.error(f"Error in preprocessing data: {str(e)}")
        raise e

@app.route('/api/promotion_predictions', methods=['GET'])
def get_promotion_predictions():
    try:
        print("==== STARTING EMPLOYEE PROMOTION MODEL TRAINING ====")
        app.logger.info("Starting promotion predictions")
        
        categories = get_categories()
        employee_data = get_employee_data()
        promotion_history = get_promotion_history()
        
        if not employee_data:
            app.logger.error("No employee data found")
            return jsonify({'success': False, 'error': 'No employee data found'}), 404

        print(f"Data retrieved: {len(employee_data)} employee records")
        df = preprocess_data(employee_data, categories, promotion_history)
        print(f"Preprocessed data shape: {df.shape}")
        
        features = [
            'administration', 'knowledge_of_work', 'quality_of_work', 'communication',
            'team', 'decision', 'dependability', 'adaptability', 'leadership',
            'customer', 'human_relations', 'personal_appearance', 'safety',
            'discipline', 'potential_growth', 'tardiness', 'tardy', 'comb_ab_hd',
            'comb_uab_uhd', 'minor', 'grave', 'suspension', 'performance',
            'manager_input', 'psa_input', 'attendance_score', 'discipline_score',
            'performance_score', 'manager_score', 'psa_score', 'tenure_years',
            'promotion_count', 'days_since_promotion'
        ]
        
        X = df[features].fillna(0)
        y = df['promotion_flag']
        
        print(f"Target distribution: {y.value_counts().to_dict()}")
        
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        print(f"Training set: {X_train.shape[0]} samples")
        print(f"Test set: {X_test.shape[0]} samples")
        
        print("\n==== TRAINING MODEL ====")
        import time
        
        # Let's go back to the simpler XGBClassifier approach but manually implement
        # the iterations with delay
        model = xgb.XGBClassifier(
            objective='binary:logistic',
            random_state=42,
            eval_metric=['error', 'logloss'],
            early_stopping_rounds=10
        )
        
        # Set up evaluation metrics to track
        eval_set = [(X_train, y_train), (X_test, y_test)]
        
        # Print header for training progress
        print("\nTraining Progress by Epoch:")
        print("-" * 100)
        print("Iter | Time  | Train Error | Test Error | Train Loss  | Test Loss   | Train Acc  | Test Acc   | Status")
        print("-" * 100)
        
        # Initialize tracking for logging
        start_time = time.time()
        
        # Get the number of estimators (trees) to build
        n_estimators = model.get_params()['n_estimators']
        if n_estimators is None or n_estimators <= 0:
            n_estimators = 100  # Default value
        
        # Set a low value for n_estimators initially, then manually add more
        model.set_params(n_estimators=1)
        
        # Perform the first fit to initialize the model
        model.fit(X_train, y_train, eval_set=eval_set, verbose=False)
        
        current_iter = 1
        best_score = float('inf')
        best_iter = 0
        no_improvement = 0
        early_stopping_rounds = 10
        
        # Function to calculate accuracy manually
        def calculate_accuracy(model, X, y):
            y_pred = model.predict(X)
            return accuracy_score(y, y_pred)
        
        # Now incrementally add more trees with delays
        while current_iter < n_estimators:
            iter_start = time.time()
            
            # Add one more tree
            model.n_estimators += 1
            model.fit(X_train, y_train, eval_set=eval_set, verbose=False, xgb_model=model.get_booster())
            
            # Get current scores
            results = model.evals_result()
            
            # Calculate accuracies
            train_accuracy = calculate_accuracy(model, X_train, y_train)
            test_accuracy = calculate_accuracy(model, X_test, y_test)
            
            # Check if results dictionary has the expected structure
            if results and 'validation_0' in results:
                # Extract metrics
                train_error = results['validation_1']['error'][-1] if 'error' in results['validation_1'] else None
                test_error = results['validation_0']['error'][-1] if 'error' in results['validation_0'] else None
                train_logloss = results['validation_1']['logloss'][-1] if 'logloss' in results['validation_1'] else None
                test_logloss = results['validation_0']['logloss'][-1] if 'logloss' in results['validation_0'] else None
                
                # For early stopping, use test logloss
                current_score = test_logloss if test_logloss is not None else float('inf')
                
                # Early stopping check
                if current_score < best_score:
                    best_score = current_score
                    best_iter = current_iter
                    no_improvement = 0
                    status = "Improved"
                else:
                    no_improvement += 1
                    status = f"No improvement ({no_improvement}/{early_stopping_rounds})"
                
                # Print progress with all metrics
                iter_time = time.time() - iter_start
                print(f"{current_iter:4d} | {iter_time:5.2f}s | "
                      f"{train_error:.6f} | {test_error:.6f} | "
                      f"{train_logloss:.6f} | {test_logloss:.6f} | "
                      f"{train_accuracy:.6f} | {test_accuracy:.6f} | {status}")
                
                # Check for early stopping
                if no_improvement >= early_stopping_rounds:
                    print(f"\nEarly stopping at iteration {current_iter} (best was {best_iter})")
                    break
            else:
                # If we can't access the evaluation results properly, just print progress
                iter_time = time.time() - iter_start
                print(f"{current_iter:4d} | {iter_time:5.2f}s | "
                      f"------- | ------- | "
                      f"------- | ------- | "
                      f"{train_accuracy:.6f} | {test_accuracy:.6f} | Completed")
            
            current_iter += 1
            
            # Add delay between iterations
            time.sleep(0.2)
        
        # Set the model back to the best iteration if we stopped early
        if no_improvement >= early_stopping_rounds and best_iter < current_iter:
            print(f"Reverting to best iteration: {best_iter}")
            model.n_estimators = best_iter
        
        # Print model evaluation
        y_pred = model.predict(X_test)
        print("\n==== MODEL EVALUATION ====")
        print(f"Accuracy: {accuracy_score(y_test, y_pred):.4f}")
        print(f"Precision: {precision_score(y_test, y_pred):.4f}")
        print(f"Recall: {recall_score(y_test, y_pred):.4f}")
        print(f"F1 Score: {f1_score(y_test, y_pred):.4f}")
        
        print("\n==== TOP FEATURE IMPORTANCE ====")
        importance = model.feature_importances_
        indices = np.argsort(importance)[::-1]
        for i in range(min(10, len(features))):
            print(f"{features[indices[i]]}: {importance[indices[i]]:.4f}")
        
        df['promotion_probability'] = model.predict_proba(X)[:, 1]
        
        print("\n==== CALCULATING SHAP VALUES ====")
        explainer = shap.Explainer(model)
        shap_values = explainer(X)
        
        results = []
        for idx, row in df.iterrows():
            emp_promo_history = [
                p for p in promotion_history if p['emp_id'] == row['emp_id']]
            
            employee_shap = {
                'features': features,
                'values': shap_values[idx].values.tolist(),
                'base_value': float(shap_values[idx].base_values),
                'data': X.iloc[idx].values.tolist()
            }
            
            results.append({
                'emp_id': int(row['emp_id']),
                'emp_name': row['emp_name'],
                'position': row['position_name'],
                'department': row['dept_name'],
                'total_score': float(row['total_score']),
                'promotion_probability': float(row['promotion_probability']),
                'promotion_history': emp_promo_history,
                'shap_explanation': employee_shap,
                'details': {
                    'attendance': {
                        'score': float(row['attendance_score']),
                        'tardiness': int(row['tardiness']),
                        'absences': int(row['comb_ab_hd'])
                    },
                    'discipline': {
                        'score': float(row['discipline_score']),
                        'minor_offenses': int(row['minor']),
                        'grave_offenses': int(row['grave'])
                    },
                    'performance': {
                        'score': float(row['performance_score']),
                        'total': int(row['performance'])
                    },
                    'tenure': {
                        'years': float(row.get('tenure_years', 0)),
                        'promotion_count': int(row.get('promotion_count', 0)),
                        'days_since_last_promotion': int(row.get('days_since_promotion', -1)) if not pd.isna(row.get('days_since_promotion')) else None
                    }
                }
            })

        metrics = {
            'accuracy': float(accuracy_score(y_test, model.predict(X_test))),
            'precision': float(precision_score(y_test, model.predict(X_test))),
            'recall': float(recall_score(y_test, model.predict(X_test))),
            'f1': float(f1_score(y_test, model.predict(X_test))),
            'feature_importance': dict(zip(features, model.feature_importances_.tolist()))
        }

        promotion_stats = {
            'total_promotions': len(promotion_history),
            'promotions_by_department': df[df['has_been_promoted'] == 1].groupby('dept_name').size().to_dict(),
            'avg_time_between_promotions': float(df[df['promotion_count'] > 1]['days_since_promotion'].mean()) if not df[df['promotion_count'] > 1].empty else None
        }

        print("\n==== MODEL TRAINING COMPLETE ====")
        print(f"Total training time: {time.time() - start_time:.2f} seconds")
        
        response = {
            'success': True,
            'data': results,
            'model_metrics': metrics,
            'promotion_stats': promotion_stats
        }

        return jsonify(response)

    except Exception as e:
        app.logger.error(f"Error in promotion predictions: {str(e)}\n{traceback.format_exc()}")
        print(f"ERROR: {str(e)}")
        print(traceback.format_exc())
        return jsonify({
            'success': False,
            'error': str(e),
            'traceback': traceback.format_exc()
        }), 500
    
@app.route("/api/get_categories", methods=['GET'])
def get_categories_api():
    try:
        categories = get_categories()
        return jsonify(categories)
    except Exception as e:
        return jsonify({"error": str(e)}), 500
    
def calculate_demographic_metrics(df, column):
    """Calculate fairness metrics for a specific demographic column"""
    try:
        # Skip if column doesn't exist or has no variation
        if column not in df.columns or len(df[column].unique()) < 2:
            return {'error': f'Not enough data for {column} analysis'}
        
        groups = []
        overall_promotion_rate = df['promotion_flag'].mean()
        
        # Add observed=True to silence the warning
        for name, group in df.groupby(column, observed=True):
            # Skip small groups (less than 5% of total or 5 employees)
            if len(group) < max(5, 0.05 * len(df)):
                continue
                
            # Clean up group names (especially for department)
            clean_name = str(name).strip()
            if not clean_name or clean_name == 'nan':
                continue
                
            promotion_rate = group['promotion_flag'].mean()
            parity_diff = abs(promotion_rate - overall_promotion_rate)
            impact_ratio = promotion_rate / overall_promotion_rate if overall_promotion_rate > 0 else 1
            
            groups.append({
                'name': clean_name,
                'size': len(group),
                'promotion_rate': float(promotion_rate),
                'parity_diff': float(parity_diff),
                'impact_ratio': float(impact_ratio)
            })
        
        if len(groups) < 2:
            return {'error': f'Not enough comparable groups in {column}'}
        
        # Sort by disparity (descending)
        groups_sorted = sorted(groups, key=lambda x: x['parity_diff'], reverse=True)
        
        return {
            'parity_diff': max(g['parity_diff'] for g in groups_sorted),
            'impact_ratio': min(g['impact_ratio'] for g in groups_sorted),
            'groups': groups_sorted
        }
    except Exception as e:
        return {'error': f'Error processing {column}: {str(e)}'}
    
@app.route('/api/fairness_metrics', methods=['GET'])
def get_fairness_metrics():
    try:
        categories = get_categories()
        employee_data = get_employee_data()
        promotion_history = get_promotion_history()  # Add this line to get promotion history
        df = preprocess_data(employee_data, categories, promotion_history)  # Now passing all required arguments
        
        metrics = {
            'overall_score': 0,
            'max_disparity': 0,
            'max_disparity_group': {'dimension': 'N/A', 'group': 'N/A'},
            'min_disparity': 0,
            'min_disparity_group': {'dimension': 'N/A', 'group': 'N/A'},
            'equal_opportunity': 0,
            'predictive_parity': 0,
            'false_positive_balance': 0
        }

        # Calculate metrics for available demographics
        demographic_results = {}
        valid_demographics = []
        
        potential_demographics = {
            'gender': 'gender',
            'age': 'age_group',
            'department': 'dept_name'
        }
        
        for display_name, col_name in potential_demographics.items():
            if col_name in df.columns:
                result = calculate_demographic_metrics(df, col_name)
                if 'error' not in result:
                    demographic_results[display_name] = result
                    valid_demographics.append(display_name)
        
        if valid_demographics:
            # Calculate composite metrics
            all_parity_diffs = []
            all_impact_ratios = []
            
            for demo in valid_demographics:
                all_parity_diffs.append(demographic_results[demo]['parity_diff'])
                all_impact_ratios.append(demographic_results[demo]['impact_ratio'])
            
            if all_parity_diffs:
                metrics['max_disparity'] = max(all_parity_diffs)
                metrics['min_disparity'] = min(all_parity_diffs)
                
                # Find groups with max/min disparity
                for demo in valid_demographics:
                    demo_data = demographic_results[demo]
                    if demo_data['parity_diff'] == metrics['max_disparity']:
                        metrics['max_disparity_group'] = {
                            'dimension': demo,
                            'group': demo_data['groups'][0]['name']
                        }
                    if demo_data['parity_diff'] == metrics['min_disparity']:
                        metrics['min_disparity_group'] = {
                            'dimension': demo,
                            'group': demo_data['groups'][-1]['name']
                        }
            
            # Calculate fairness indicators (simplified example)
            if 'gender' in demographic_results:
                gender_data = demographic_results['gender']
                metrics['equal_opportunity'] = max(g['parity_diff'] for g in gender_data['groups'])
                metrics['predictive_parity'] = max(1/g['impact_ratio'] for g in gender_data['groups'])
                metrics['false_positive_balance'] = max(g['parity_diff'] for g in gender_data['groups'])
            
            # Calculate overall score (0-100 scale)
            if all_parity_diffs:
                avg_disparity = sum(all_parity_diffs) / len(all_parity_diffs)
                metrics['overall_score'] = max(0, min(100, 100 - (avg_disparity * 500)))
            
            metrics.update(demographic_results)
        
        return jsonify({'success': True, 'metrics': metrics})
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)})

# Departments API
@app.route('/api/departments', methods=['GET'])
def get_departments():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT * FROM tbl_department")
        departments = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        return jsonify(departments)
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# Positions API
@app.route('/api/positions', methods=['GET'])
def get_positions():
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT * FROM tbl_positions")
        positions = cursor.fetchall()
        
        cursor.close()
        conn.close()
        
        return jsonify(positions)
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# Employee CRUD API
@app.route('/api/employees', methods=['GET'])
def get_employees():
    try:
        # Get pagination and filter parameters from request
        page = request.args.get('page', default=1, type=int)
        per_page = request.args.get('per_page', default=10, type=int)
        department = request.args.get('department', default=None)
        status = request.args.get('status', default=None)
        search = request.args.get('search', default=None)
        
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        # Base query
        query = """
        SELECT e.*, d.dept_name, p.position_name 
        FROM tbl_employee_details e
        LEFT JOIN tbl_department d ON e.department = d.dept_id
        LEFT JOIN tbl_positions p ON e.position = p.position_id
        WHERE 1=1 AND e.active_status = 1
        """
        
        params = []
        
        # Add filters
        if department:
            query += " AND d.dept_name = %s"
            params.append(department)
        if status:
            query += " AND e.emp_status = %s"
            params.append(status)
        if search:
            query += " AND (e.emp_name LIKE %s OR p.position_name LIKE %s OR d.dept_name LIKE %s)"
            params.extend([f"%{search}%", f"%{search}%", f"%{search}%"])
        
        # Add pagination
        query += " LIMIT %s OFFSET %s"
        offset = (page - 1) * per_page
        params.extend([per_page, offset])
        
        cursor.execute(query, params)
        employees = cursor.fetchall()
        
        # Get total count for pagination
        count_query = "SELECT COUNT(*) as total FROM tbl_employee_details e WHERE 1=1 AND e.active_status = 1"
        count_params = []
        
        if department:
            count_query += " AND e.department = (SELECT dept_id FROM tbl_department WHERE dept_name = %s)"
            count_params.append(department)
        if status:
            count_query += " AND e.emp_status = %s"
            count_params.append(status)
        if search:
            count_query += " AND (e.emp_name LIKE %s OR e.position IN (SELECT position_id FROM tbl_positions WHERE position_name LIKE %s) OR e.department IN (SELECT dept_id FROM tbl_department WHERE dept_name LIKE %s))"
            count_params.extend([f"%{search}%", f"%{search}%", f"%{search}%"])
        
        cursor.execute(count_query, count_params)
        total = cursor.fetchone()['total']
        
        cursor.close()
        conn.close()
        
        return jsonify({
            'employees': employees,
            'total': total,
            'page': page,
            'per_page': per_page
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/employees/<int:emp_id>', methods=['PUT'])
def update_employee(emp_id):
    try:
        data = request.get_json()
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        
        # Calculate tenure if not provided
        if 'tenure' not in data and 'start_date' in data:
            try:
                from datetime import datetime
                start_date = datetime.strptime(data['start_date'], '%Y-%m-%d')
                today = datetime.now()
                delta = today - start_date
                years = delta.days // 365
                days = delta.days % 365
                data['tenure'] = f"{years} year{'s' if years != 1 else ''} {days} day{'s' if days != 1 else ''}"
            except:
                data['tenure'] = ''
        
        query = """
        UPDATE tbl_employee_details 
        SET emp_name = %s, 
            age = %s, 
            gender = %s, 
            emp_status = %s, 
            department = %s, 
            position = %s, 
            start_date = %s, 
            regularization = %s,
            tenure = %s
        WHERE emp_id = %s
        """
        params = (
            data['emp_name'],
            data['age'],
            data['gender'],
            data['emp_status'],
            data['department'],
            data['position'],
            data['start_date'],
            data['regularization'],
            data.get('tenure', ''),
            emp_id
        )
        
        cursor.execute(query, params)
        conn.commit()
        
        cursor.close()
        conn.close()
        
        return jsonify({'success': True, 'message': 'Employee updated successfully'})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/employees/<int:emp_id>', methods=['DELETE'])
def delete_employee(emp_id):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        
        cursor.execute("UPDATE tbl_employee_details SET active_status = 2 WHERE emp_id = %s", (emp_id,))
        conn.commit()
        
        cursor.close()
        conn.close()
        
        return jsonify({'success': True, 'message': 'Employee deleted successfully'})
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    
@app.route('/api/employees/<int:emp_id>', methods=['GET'])
def get_employee(emp_id):
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        query = """
        SELECT e.*, d.dept_name, p.position_name
        FROM tbl_employee_details e
        LEFT JOIN tbl_department d ON e.department = d.dept_id
        LEFT JOIN tbl_positions p ON e.position = p.position_id
        WHERE e.emp_id = %s AND active_status = 1
        """
        
        cursor.execute(query, (emp_id,))
        employee = cursor.fetchone()
        
        if not employee:
            return jsonify({'error': 'Employee not found'}), 404
            
        promo_query = """
        SELECT 
            ph.*, 
            prev_pos.position_name as previous_position,
            np.position_name as new_position, 
            prev_dept.dept_name as previous_department,
            nd.dept_name as new_department
        FROM tbl_promotion_history ph
        LEFT JOIN tbl_positions np ON ph.new_position_id = np.position_id
        LEFT JOIN tbl_department nd ON ph.new_department_id = nd.dept_id
        LEFT JOIN tbl_positions prev_pos ON ph.previous_position_id = prev_pos.position_id
        LEFT JOIN tbl_department prev_dept ON ph.previous_department_id = prev_dept.dept_id
        WHERE ph.emp_id = %s
        ORDER BY ph.promotion_date DESC
        """
        cursor.execute(promo_query, (emp_id,))
        promotion_history = cursor.fetchall()
        
        employee['promotion_history'] = promotion_history
        
        cursor.close()
        conn.close()
        
        return jsonify(employee)
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/api/employee_data', methods=['GET'])
def employee_data():
    try:
        data = get_employee_data()
        return jsonify(data)
    except Exception as e:
        return jsonify({"error": str(e)}), 500


# ----------------- 
@app.route('/api/evaluation/employees', methods=['GET'])
def get_evaluation_employees():
    """Get all employees for evaluation dropdown"""
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT emp_id, emp_name FROM tbl_employee_details ORDER BY emp_name")
        employees = cursor.fetchall()
        
        cursor.execute("SELECT * FROM Evaluation_Criteria")
        criteria = cursor.fetchall()
        
        cursor.execute("SELECT * FROM Performance_Rating_Scale ORDER BY rating DESC")
        rating_scale = cursor.fetchall()
        
        return jsonify({
            'success': True,
            'employees': employees,
            'criteria': criteria,
            'rating_scale': rating_scale
        })
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500
    finally:
        cursor.close()
        conn.close()

@app.route('/api/evaluation/employee/<int:emp_id>', methods=['GET'])
def get_employee_evaluation(emp_id):
    """Get evaluation data for a specific employee"""
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT e.*, p.position_name, d.dept_name 
            FROM tbl_employee_details e
            LEFT JOIN tbl_positions p ON e.position = p.position_id
            LEFT JOIN tbl_department d ON e.department = d.dept_id
            WHERE e.emp_id = %s
        """, (emp_id,))
        employee = cursor.fetchone()
        
        if not employee:
            return jsonify({'success': False, 'error': 'Employee not found'}), 404
        
        data = {
            'employee': employee,
            'eval_id': None,
            'attendance': {'tardiness': 0, 'tardy': 0, 'comb_ab_hd': 0, 'comb_uab_uhd': 0, 'AB': 0, 'UAB': 0, 'HD': 0, 'UHD': 0},
            'discipline': {'minor': 0, 'grave': 0, 'suspension': 0},
            'evaluation': {metric: 1 for metric in [
                'administration', 'knowledge_of_work', 'quality_of_work',
                'communication', 'team', 'decision', 'dependability',
                'adaptability', 'leadership', 'customer', 'human_relations',
                'personal_appearance', 'safety', 'discipline', 'potential_growth'
            ]},
            'other_metrics': {'performance': 0, 'manager_input': 0, 'psa_input': 0},
            'has_data': False 
        }
        

        has_data = False
        
        cursor.execute("SELECT * FROM tbl_eval_attendance WHERE emp_id = %s", (emp_id,))
        eval_id = 0
        if attendance := cursor.fetchone():
            data['attendance'] = attendance
            has_data = True
            eval_id = attendance.get('eval_id', None)
            data['eval_id'] = eval_id

            if eval_id:
                cursor.execute("SELECT * FROM tbl_eval_discipline WHERE eval_id = %s", (eval_id,))
                if discipline := cursor.fetchone():
                    data['discipline'] = discipline
                    has_data = True

        cursor.execute("SELECT * FROM tbl_evaluation WHERE emp_id = %s", (emp_id,))
        if evaluation_data := cursor.fetchone():
            data['evaluation'] = evaluation_data
            has_data = True
        
        if eval_id:
            cursor.execute("SELECT * FROM tbl_eval_others WHERE eval_id = %s", (eval_id,))
            if other_metrics := cursor.fetchone():
                data['other_metrics'] = other_metrics
                has_data = True
        
        data['has_data'] = has_data
        
        return jsonify({'success': True, 'data': data})
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500
    finally:
        cursor.close()
        conn.close()

@app.route('/api/evaluation/calculate', methods=['POST'])
def calculate_evaluation():
    """Calculate evaluation scores"""
    try:
        data = request.get_json()
        
        # Calculate attendance rating
        attendance_rating = calculate_attendance_rating(
            data.get('tardiness_instances', 0),
            data.get('tardy_minutes', 0),
            data.get('absenteeism', 0),
            data.get('uab_uhd', 0)
        )
        
        # Calculate discipline rating
        discipline_rating = calculate_discipline_rating(
            data.get('minor', 0),
            data.get('grave', 0),
            data.get('suspension', 0)
        )
        
        # Calculate weighted score
        weighted_score = (
            (attendance_rating * 0.20) + 
            (discipline_rating * 0.20) + 
            (data.get('performance_eval', 0) * 0.30) + 
            (data.get('manager_input', 0) * 0.10) + 
            (data.get('psa_input', 0) * 0.20
        ))
        
        # Get overall rating
        overall_rating = get_overall_rating(weighted_score)
        
        return jsonify({
            'success': True,
            'results': {
                'attendance_rating': attendance_rating,
                'discipline_rating': discipline_rating,
                'weighted_score': weighted_score,
                'overall_rating': overall_rating
            }
        })
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/api/evaluation/save', methods=['POST'])
def save_evaluation():
    """Save evaluation data including attendance updates"""
    try:
        data = request.get_json()
        
        if not data.get('emp_id'):
            return jsonify({'success': False, 'error': 'Employee ID is required'}), 400
            
        emp_id = data['emp_id']
        action = 'updated'  
        eval_id = None
        
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT eval_id FROM tbl_eval_attendance 
            WHERE emp_id = %s
        """, (emp_id,))
        attendance_record = cursor.fetchone()
        
        if attendance_record:
            eval_id = attendance_record['eval_id']
            cursor.execute("""
                UPDATE tbl_eval_attendance SET
                    tardiness = %s,
                    tardy = %s,
                    comb_ab_hd = %s,
                    comb_uab_uhd = %s,
                    AB = %s,
                    UAB = %s,
                    HD = %s,
                    UHD = %s
                WHERE emp_id = %s
            """, (
                data.get('tardiness_instances', 0),
                data.get('tardy_minutes', 0),
                data.get('absenteeism', 0),
                data.get('uab_uhd', 0),
                data.get('AB', 0),
                data.get('UAB', 0),
                data.get('HD', 0),
                data.get('UHD', 0),
                emp_id
            ))
        else:
            action = 'created'
            cursor.execute("""
                INSERT INTO tbl_eval_attendance (
                    emp_id, tardiness, tardy, comb_ab_hd, 
                    comb_uab_uhd, AB, UAB, HD, UHD
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                emp_id,
                data.get('tardiness_instances', 0),
                data.get('tardy_minutes', 0),
                data.get('absenteeism', 0),
                data.get('uab_uhd', 0),
                data.get('AB', 0),
                data.get('UAB', 0),
                data.get('HD', 0),
                data.get('UHD', 0)
            ))
            eval_id = cursor.lastrowid
        
        cursor.execute("SELECT 1 FROM tbl_evaluation WHERE emp_id = %s", (emp_id,))
        if cursor.fetchone():
            cursor.execute("""
                UPDATE tbl_evaluation SET
                    administration = %s,
                    knowledge_of_work = %s,
                    quality_of_work = %s,
                    communication = %s,
                    team = %s,
                    decision = %s,
                    dependability = %s,
                    adaptability = %s,
                    leadership = %s,
                    customer = %s,
                    human_relations = %s,
                    personal_appearance = %s,
                    safety = %s,
                    discipline = %s,
                    potential_growth = %s,
                    highlight = %s,
                    lowlight = %s
                WHERE emp_id = %s
            """, (
                data.get('administration', 1),
                data.get('knowledge_of_work', 1),
                data.get('quality_of_work', 1),
                data.get('communication', 1),
                data.get('team', 1),
                data.get('decision', 1),
                data.get('dependability', 1),
                data.get('adaptability', 1),
                data.get('leadership', 1),
                data.get('customer', 1),
                data.get('human_relations', 1),
                data.get('personal_appearance', 1),
                data.get('safety', 1),
                data.get('discipline', 1),
                data.get('potential_growth', 1),
                data.get('highlight', ''),
                data.get('lowlight', ''),
                emp_id
            ))
        else:

            cursor.execute("""
                INSERT INTO tbl_evaluation (
                    emp_id, administration, knowledge_of_work, quality_of_work, 
                    communication, team, decision, dependability, adaptability, 
                    leadership, customer, human_relations, personal_appearance, 
                    safety, discipline, potential_growth, highlight, lowlight
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                emp_id,
                data.get('administration', 1),
                data.get('knowledge_of_work', 1),
                data.get('quality_of_work', 1),
                data.get('communication', 1),
                data.get('team', 1),
                data.get('decision', 1),
                data.get('dependability', 1),
                data.get('adaptability', 1),
                data.get('leadership', 1),
                data.get('customer', 1),
                data.get('human_relations', 1),
                data.get('personal_appearance', 1),
                data.get('safety', 1),
                data.get('discipline', 1),
                data.get('potential_growth', 1),
                data.get('highlight', ''),
                data.get('lowlight', '')
            ))

        cursor.execute("SELECT 1 FROM tbl_eval_discipline WHERE eval_id = %s", (eval_id,))
        if cursor.fetchone():
            cursor.execute("""
                UPDATE tbl_eval_discipline SET
                    minor = %s,
                    grave = %s,
                    suspension = %s
                WHERE eval_id = %s
            """, (
                data.get('minor', 0),
                data.get('grave', 0),
                data.get('suspension', 0),
                eval_id
            ))
        else:
            cursor.execute("""
                INSERT INTO tbl_eval_discipline (
                    eval_id, minor, grave, suspension
                ) VALUES (%s, %s, %s, %s)
            """, (
                eval_id,
                data.get('minor', 0),
                data.get('grave', 0),
                data.get('suspension', 0)
            ))

        # Handle other metrics (update or insert)
        cursor.execute("SELECT 1 FROM tbl_eval_others WHERE eval_id = %s", (eval_id,))
        if cursor.fetchone():
            cursor.execute("""
                UPDATE tbl_eval_others SET
                    performance = %s,
                    manager_input = %s,
                    psa_input = %s
                WHERE eval_id = %s
            """, (
                data.get('performance_eval', 0),
                data.get('manager_input', 0),
                data.get('psa_input', 0),
                eval_id
            ))
        else:
            cursor.execute("""
                INSERT INTO tbl_eval_others (
                    eval_id, performance, manager_input, psa_input
                ) VALUES (%s, %s, %s, %s)
            """, (
                eval_id,
                data.get('performance_eval', 0),
                data.get('manager_input', 0),
                data.get('psa_input', 0)
            ))
        
        conn.commit()
        
        return jsonify({
            'success': True, 
            'message': f'Evaluation {action} successfully',
            'action': action,
            'emp_id': emp_id,
            'eval_id': eval_id
        })
        
    except Exception as e:
        conn.rollback()
        app.logger.error(f"Error saving evaluation: {str(e)}")
        return jsonify({'success': False, 'error': str(e)}), 500
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'conn' in locals():
            conn.close()

def calculate_attendance_rating(tardiness_instances, tardy_minutes, absenteeism, uab_uhd):
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(dictionary=True)
    
    cursor.execute("""
        SELECT rate FROM Tardiness_Rating 
        WHERE (%s BETWEEN min_instances AND IFNULL(max_instances, %s))
        AND (%s BETWEEN min_minutes AND IFNULL(max_minutes, %s))
        AND (%s BETWEEN min_absenteeism AND IFNULL(max_absenteeism, %s))
        AND (%s BETWEEN min_uab_uhd AND IFNULL(max_uab_uhd, %s))
        ORDER BY rate DESC LIMIT 1
    """, (
        tardiness_instances, tardiness_instances,
        tardy_minutes, tardy_minutes,
        absenteeism, absenteeism,
        uab_uhd, uab_uhd
    ))
    
    result = cursor.fetchone()
    cursor.close()
    conn.close()
    
    return result['rate'] if result else 1

def calculate_discipline_rating(minor, grave, suspension):
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(dictionary=True)
    
    cursor.execute("""
        SELECT rate FROM Discipline_Grave 
        WHERE (%s BETWEEN min_minor AND IFNULL(max_minor, %s))
        AND (%s BETWEEN min_grave AND IFNULL(max_grave, %s))
        AND (%s BETWEEN min_suspension AND IFNULL(max_suspension, %s))
        ORDER BY rate DESC LIMIT 1
    """, (
        minor, minor,
        grave, grave,
        suspension, suspension
    ))
    
    result = cursor.fetchone()
    cursor.close()
    conn.close()
    
    return result['rate'] if result else 1

def get_overall_rating(score):
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(dictionary=True)
    
    cursor.execute("""
        SELECT rating FROM Performance_Rating_Scale 
        WHERE (%s BETWEEN min_score AND IFNULL(max_score, %s))
        ORDER BY rating DESC LIMIT 1
    """, (score, score))
    
    result = cursor.fetchone()
    cursor.close()
    conn.close()
    
    return result['rating'] if result else 1



if __name__ == '__main__':
    app.run(debug=True, port=8800)