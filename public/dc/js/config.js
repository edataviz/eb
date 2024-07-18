//config.js
var routes = {
    "R-1": {
        id: 1,
        name: "Route 1",
		complete: 1,
		total: 10,
    },
    "R-2": {
        id: 2,
        name: "Route 2",
		complete: 2,
		total: 10,
    },
    "R-3": {
        id: 3,
        name: "Route 3",
		complete: 3,
		total: 10,
    },
};

var points = {
    "P-1.1": {
        id: 1,
        route_id: 1,
        name: "Point 1.1",
		FL: 10,
		EU: 10,
		TA: 10,
		EQ: 10,
        complete: true,
    },
    "P-1.2": {
        id: 2,
        route_id: 1,
        name: "Point 1.2",
		FL: 20,
		EU: 20,
		TA: 20,
		EQ: 20,
        complete: false,
    },
    "P-1.3": {
        id: 3,
        route_id: 2,
        name: "Point 1.3",
		FL: 30,
		EU: 30,
		TA: 30,
		EQ: 30,
        complete: false,
    },
    "P-1.4": {
        id: 4,
        route_id: 3,
        name: "Point 1.4",
		FL: 40,
		EU: 40,
		TA: 40,
		EQ: 40,
        complete: false,
    },
};

var objects = {
    "O-1.1.1": {
        id: 1,
        point_id: 1,
        type: "FL",
        name: "Flow 1",
    },
    "O-1.1.2": {
        id: 2,
        point_id: 1,
        type: "EU",
        name: "Energy Unit 1",
    },
    "O-1.1.3": {
        id: 3,
        point_id: 2,
        type: "FL",
        name: "Flow 2",
    },
    "O-1.1.4": {
        id: 4,
        point_id: 2,
        type: "EU",
        name: "Energy Unit 2",
    },
    "O-1.1.5": {
        id: 5,
        point_id: 3,
        type: "EU",
        name: "Energy Unit 3",
    },
};

var object_types = {
    "FL": "FLOW",
    "EU": "ENERGY UNIT",
    "TA": "TANK",
    "EQ": "EQUIPMENT"
};

var data_types = {
    "n": "Number",
    "t": "Text",
    "d": "Date"
};

var control_types = {
    "n": "Number input",
    "t": "Text input",
    "d": "Date picker",
    "l": "List"
};

var lists = {
    "VolUOM": "<option value='1'>kL</option><option value='2'>scm3</option><option value='3'>cb</option><option value='4'>f</option>",
    "MassUOM": "<option value='1'>kg</option><option value='2'>pound</option><option value='3'>ton</option>",
};

var object_attrs = {
    "FL": {
        "OCCUR_DATE": {
            name: "Occur Date",
            data_type: "d",
            control_type: "d",
        },
        "ACTIVE_HRS": {
            name: "Active Hrs",
            data_type: "n",
            control_type: "n",
        },
        "GRS_VOL": {
            name: "Gross Vol",
            data_type: "n",
            control_type: "n",
        },
    },
    "EU": {
        "OCCUR_DATE": {
            name: "Occur Date",
            data_type: "d",
            control_type: "d",
        },
        "ACTIVE_HRS": {
            name: "Active Hrs",
            data_type: "n",
            control_type: "n",
        },
        "GRS_VOL": {
            name: "Gross Vol",
            data_type: "n",
            control_type: "n",
        },
        "VOL_UOM": {
            name: "Volume Unit",
            data_type: "n",
            control_type: "l",
            list: "VolUOM",
        },
        "GRS_MASS": {
            name: "Gross Mass",
            data_type: "n",
            control_type: "n",
        },
        "MASS_UOM": {
            name: "Mass Unit",
            data_type: "n",
            control_type: "l",
            list: "MassUOM",
        },
        "COMMENT": {
            name: "Comment",
            data_type: "t",
            control_type: "t",
        },
    }
};

var object_details = {
    "1": {
		id:"O-1.1.1",
        OCCUR_DATE: "2017-03-15",
        ACTIVE_HRS: 10,
        GRS_VOL: 1000,
    },
    "2": {
		id:"O-1.1.2",
        OCCUR_DATE: "2017-03-15",
        ACTIVE_HRS: 10,
        GRS_VOL: 1000,
		VOL_UOM: 1,
		GRS_MASS: 1000,
		MASS_UOM: 1,
		COMMENT: "comment 2",
    },
    "3": {
		id:"O-1.1.3",
        OCCUR_DATE: "2017-03-12",
        ACTIVE_HRS: 5,
        GRS_VOL: 500,
    },
    "4": {
		id:"O-1.1.4",
        OCCUR_DATE: "2017-03-12",
        ACTIVE_HRS: 5,
        GRS_VOL: 500,
		VOL_UOM: 2,
		GRS_MASS: 3,
		MASS_UOM: 2,
		COMMENT: "comment 4",
    },
    "5": {
		id:"O-1.1.5",
        OCCUR_DATE: "2017-03-11",
        ACTIVE_HRS: 15,
        GRS_VOL: 1500,
		VOL_UOM: 3,
		GRS_MASS: 3,
		MASS_UOM: 3,
		COMMENT: "comment 5",
    },
};