const tbl_accounts = [
    {
      "ev_id": 1,
      "emp_id": 4,
      "administration": 5,
      "knowledge_of_work": 5,
      "quality_of_work": 5,
      "communication": 5,
      "team": 5,
      "decision": 4,
      "dependability": 4,
      "adaptability": 5,
      "leadership": 5,
      "customer": 5,
      "human_relations": 4,
      "personal_appearance": 4,
      "safety": 3,
      "discipline": 5,
      "potential_growth": 5,
      "highlight": null,
      "lowlight": null
    },
    {
      "ev_id": 2,
      "emp_id": 48,
      "administration": 3,
      "knowledge_of_work": 3,
      "quality_of_work": 5,
      "communication": 3,
      "team": 4,
      "decision": 4,
      "dependability": 5,
      "adaptability": 3,
      "leadership": 4,
      "customer": 4,
      "human_relations": 4,
      "personal_appearance": 5,
      "safety": 3,
      "discipline": 5,
      "potential_growth": 4,
      "highlight": "Taking responsibility for one's action & work outc...",
      "lowlight": "Impatience"
    }
  ]
  
  const tbl_eval_attendance = [
    {
      "eval_id": 1,
      "emp_id": 13,
      "tardiness": 0,
      "tardy": 0,
      "comb_ab_hd": 0,
      "comb_uab_uhd": 0,
      "AB": 0,
      "UAB": 0,
      "HD": 0,
      "UHD": 0
    },
    {
      "eval_id": 2,
      "emp_id": 16,
      "tardiness": 1,
      "tardy": 3,
      "comb_ab_hd": 1,
      "comb_uab_uhd": 0,
      "AB": 0,
      "UAB": 0,
      "HD": 1,
      "UHD": 0
    }
  ]

  const tbl_eval_others = [
  {
    "eval_id": 1,
    "performance": 55,
    "manager_input": null,
    "psa_input": null
  },
  {
    "eval_id": 2,
    "performance": 54,
    "manager_input": null,
    "psa_input": "NU"
  }
]


const sample =[
  {
    "emp_id": 4,
    "emp_name": "TABOADA, MA. ROSARIO",
    "emp_status": "REGULAR",
    "eval_id": 58,
    "tardiness": 35,
    "tardy": 184,
    "comb_ab_hd": 3,
    "comb_uab_uhd": 0,
    "AB": 1,
    "UAB": 0,
    "HD": 2,
    "UHD": 0,
    "minor": 6,
    "grave": 0,
    "suspension": 0,
    "performance": 69,
    "manager_input": null,
    "psa_input": null,
    "highlight": null,
    "lowlight": null,
    "administration": 5,
    "knowledge_of_work": 5,
    "quality_of_work": 5,
    "communication": 5,
    "team": 5,
    "decision": 4,
    "dependability": 4,
    "adaptability": 5,
    "leadership": 5,
    "customer": 5,
    "human_relations": 4,
    "personal_appearance": 4,
    "safety": 3,
    "discipline": 5,
    "potential_growth": 5,
    "position_name": "HR Admin",
    "dept_name": "Human Resources"
  },
  {
    "emp_id": 48,
    "emp_name": "DALISAY, JANE",
    "emp_status": "REGULAR",
    "eval_id": 13,
    "tardiness": 2,
    "tardy": 55,
    "comb_ab_hd": 0,
    "comb_uab_uhd": 1,
    "AB": 0,
    "UAB": 1,
    "HD": 0,
    "UHD": 0,
    "minor": 3,
    "grave": 0,
    "suspension": 0,
    "performance": 59,
    "manager_input": null,
    "psa_input": null,
    "highlight": "Taking responsibility for one's action & work outc...",
    "lowlight": "Impatience",
    "administration": 3,
    "knowledge_of_work": 3,
    "quality_of_work": 5,
    "communication": 3,
    "team": 4,
    "decision": 4,
    "dependability": 5,
    "adaptability": 3,
    "leadership": 4,
    "customer": 4,
    "human_relations": 4,
    "personal_appearance": 5,
    "safety": 3,
    "discipline": 5,
    "potential_growth": 4,
    "position_name": "HR Admin",
    "dept_name": "Human Resources"
  },
  {
    "emp_id": 52,
    "emp_name": "CULA, REANNE GAY",
    "emp_status": "REGULAR",
    "eval_id": 22,
    "tardiness": 48,
    "tardy": 472,
    "comb_ab_hd": 1,
    "comb_uab_uhd": 0,
    "AB": 1,
    "UAB": 0,
    "HD": 0,
    "UHD": 0,
    "minor": 13,
    "grave": 0,
    "suspension": 0,
    "performance": 59,
    "manager_input": 4.5,
    "psa_input": null,
    "highlight": null,
    "lowlight": null,
    "administration": 4,
    "knowledge_of_work": 5,
    "quality_of_work": 3,
    "communication": 4,
    "team": 4,
    "decision": 3,
    "dependability": 4,
    "adaptability": 4,
    "leadership": 4,
    "customer": 4,
    "human_relations": 4,
    "personal_appearance": 4,
    "safety": 3,
    "discipline": 4,
    "potential_growth": 5,
    "position_name": "HR Admin",
    "dept_name": "Human Resources"
  }
]


const evaluation_criteria = [
  {
    "category": "ATTENDANCE",
    "weight": 20.00,
    "scale": 10,
    "score": null,
    "rating": null
  },
  {
    "category": "DISCIPLINE",
    "weight": 20.00,
    "scale": 10,
    "score": null,
    "rating": null
  },
  {
    "category": "PERFORMANCE EVAL",
    "weight": 30.00,
    "scale": 10,
    "score": null,
    "rating": null
  },
  {
    "category": "MNGR INPUT",
    "weight": 10.00,
    "scale": 5,
    "score": null,
    "rating": null
  },
  {
    "category": "PSA INPUT",
    "weight": 20.00,
    "scale": 5,
    "score": null,
    "rating": null
  }
]


const tardiness_rating = [
  {
    "rate": 10,
    "max_instances": 0,
    "min_instances": 0,
    "min_minutes": 0,
    "max_minutes": 0,
    "min_absenteeism": 0,
    "max_absenteeism": 0,
    "min_uab_uhd": 0,
    "max_uab_uhd": 0
  },
  {
    "rate": 9,
    "max_instances": 7,
    "min_instances": 1,
    "min_minutes": 1,
    "max_minutes": 240,
    "min_absenteeism": 1,
    "max_absenteeism": 3,
    "min_uab_uhd": 1,
    "max_uab_uhd": 1
  },
  {
    "rate": 8,
    "max_instances": 14,
    "min_instances": 8,
    "min_minutes": 241,
    "max_minutes": 480,
    "min_absenteeism": 4,
    "max_absenteeism": 6,
    "min_uab_uhd": 2,
    "max_uab_uhd": 2
  },
  {
    "rate": 7,
    "max_instances": 21,
    "min_instances": 15,
    "min_minutes": 481,
    "max_minutes": 720,
    "min_absenteeism": 7,
    "max_absenteeism": 9,
    "min_uab_uhd": 3,
    "max_uab_uhd": 3
  },
  {
    "rate": 6,
    "max_instances": 28,
    "min_instances": 22,
    "min_minutes": 721,
    "max_minutes": 960,
    "min_absenteeism": 10,
    "max_absenteeism": 12,
    "min_uab_uhd": 4,
    "max_uab_uhd": 4
  },
  {
    "rate": 5,
    "max_instances": 35,
    "min_instances": 29,
    "min_minutes": 961,
    "max_minutes": 1200,
    "min_absenteeism": 13,
    "max_absenteeism": 15,
    "min_uab_uhd": 5,
    "max_uab_uhd": 5
  },
  {
    "rate": 4,
    "max_instances": 42,
    "min_instances": 36,
    "min_minutes": 1201,
    "max_minutes": 1440,
    "min_absenteeism": 16,
    "max_absenteeism": 18,
    "min_uab_uhd": 6,
    "max_uab_uhd": 6
  },
  {
    "rate": 3,
    "max_instances": 49,
    "min_instances": 43,
    "min_minutes": 1441,
    "max_minutes": 1680,
    "min_absenteeism": 19,
    "max_absenteeism": 21,
    "min_uab_uhd": 7,
    "max_uab_uhd": 7
  },
  {
    "rate": 2,
    "max_instances": 56,
    "min_instances": 50,
    "min_minutes": 1681,
    "max_minutes": 1920,
    "min_absenteeism": 22,
    "max_absenteeism": 24,
    "min_uab_uhd": 8,
    "max_uab_uhd": 8
  },
  {
    "rate": 1,
    "max_instances": null,
    "min_instances": 57,
    "min_minutes": 1921,
    "max_minutes": null,
    "min_absenteeism": 25,
    "max_absenteeism": null,
    "min_uab_uhd": 9,
    "max_uab_uhd": null
  }
]

const discipline_rating = [
  {
    "min_minor": 0,
    "max_minor": 0,
    "min_grave": 0,
    "max_grave": 0,
    "min_suspension": 0,
    "max_suspension": 0,
    "rate": 10
  },
  {
    "min_minor": 1,
    "max_minor": 3,
    "min_grave": 1,
    "max_grave": 1,
    "min_suspension": 1,
    "max_suspension": 3,
    "rate": 9
  },
  {
    "min_minor": 4,
    "max_minor": 5,
    "min_grave": 2,
    "max_grave": 2,
    "min_suspension": 4,
    "max_suspension": 5,
    "rate": 8
  },
  {
    "min_minor": 6,
    "max_minor": 9,
    "min_grave": 3,
    "max_grave": 3,
    "min_suspension": 6,
    "max_suspension": 9,
    "rate": 7
  },
  {
    "min_minor": 10,
    "max_minor": 12,
    "min_grave": 4,
    "max_grave": 4,
    "min_suspension": 10,
    "max_suspension": 12,
    "rate": 6
  },
  {
    "min_minor": 12,
    "max_minor": 15,
    "min_grave": 5,
    "max_grave": 5,
    "min_suspension": 12,
    "max_suspension": 15,
    "rate": 5
  },
  {
    "min_minor": 16,
    "max_minor": 18,
    "min_grave": 6,
    "max_grave": 6,
    "min_suspension": 16,
    "max_suspension": 18,
    "rate": 4
  },
  {
    "min_minor": 19,
    "max_minor": 20,
    "min_grave": 7,
    "max_grave": 7,
    "min_suspension": 19,
    "max_suspension": 20,
    "rate": 3
  },
  {
    "min_minor": 21,
    "max_minor": 23,
    "min_grave": 8,
    "max_grave": 8,
    "min_suspension": 21,
    "max_suspension": 23,
    "rate": 2
  },
  {
    "min_minor": 24,
    "max_minor": null,
    "min_grave": 9,
    "max_grave": null,
    "min_suspension": 24,
    "max_suspension": null,
    "rate": 1
  }
]

const performance_rating = [
  {
    "min_score": 70,
    "max_score": null,
    "rating": 10
  },
  {
    "min_score": 62,
    "max_score": 69,
    "rating": 9
  },
  {
    "min_score": 55,
    "max_score": 61,
    "rating": 8
  },
  {
    "min_score": 48,
    "max_score": 54,
    "rating": 7
  },
  {
    "min_score": 41,
    "max_score": 47,
    "rating": 6
  },
  {
    "min_score": 34,
    "max_score": 40,
    "rating": 5
  },
  {
    "min_score": 27,
    "max_score": 33,
    "rating": 4
  },
  {
    "min_score": 20,
    "max_score": 26,
    "rating": 3
  },
  {
    "min_score": 13,
    "max_score": 19,
    "rating": 2
  },
  {
    "min_score": null,
    "max_score": 12,
    "rating": 1
  }
]