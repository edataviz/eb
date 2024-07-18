//config.js

var routes = {
"R-1":{
    	id: 1,
	name: "Route 1",
},
"R-2":{
    	id: 2,
	name: "Route 2",
},
};

var  points = {
"P-1.1":{
    	id: 1,
	route_id: 1
	name: "Point 1.1",
	complete: true,
},
"P-1.2":{
    	id: 2,
	route_id: 1
	name: "Point 1.2",
	complete: false,
},
"P-1.3":{
    	id: 3,
	route_id: 1
	name: "Point 1.3",
	complete: false,
},
"P-1.4":{
    	id: 4,
	route_id: 1
	name: "Point 1.4",
	complete: false,
},
};

var objects = {
"O-1.1.1":{
    	id: 1,
	point_id: 1
    	type: "FL",
	name: "Flow 1",
},
"O-1.1.2":{
    	id: 2,
	point_id: 1
    	type: "EU",
	name: "Energy Unit 1",
},
"O-1.1.3":{
    	id: 3,
	point_id: 1
    	code: "FL",
	name: "Flow 2",
},
"O-1.1.4":{
    	id: 2,
	point_id: 1
    	type: "EU",
	name: "Energy Unit 2",
},
"O-1.1.5":{
    	id: 5,
	point_id: 1
    	type: "EU",
	name: "Energy Unit 3",
},
};

var object_types = {"FL":"FLOW", "EU":"ENERGY UNIT", "TA": "TANK", "EQ":"EQUIPMENT"};

var data_types = {"n":"Number", "t":"Text", "d":"Date"};

var control_types = {"n":"Number input", "t":"Text input", "d":"Date picker", "l":"List"};

var lists = {
	"VolUOM":"<option value='1'>kL</option><option value='2'>scm3</option><option value='3'>cb</option><option value='4'>f</option>",
	"MassUOM":"<option value='1'>kg</option><option value='2'>pound</option><option value='3'>ton</option>",
};

var object_attrs = {
"FL":{
	"OCCUR_DATE":{
		name:"Occur Date",
		data_type:"d",
		control_type:"d",
	},
	"ACTIVE_HRS":{
		name:"Active Hrs",
		data_type:"n",
		control_type:"n",
	},
	"GRS_VOL":{
		name:"Gross Vol",
		data_type:"n",
		control_type:"n",
	},
},
"EU":{
	"OCCUR_DATE":{
		name:"Occur Date",
		data_type:"d",
		control_type:"d",
	},
	"ACTIVE_HRS":{
		name:"Active Hrs",
		data_type:"n",
		control_type:"n",
	},
	"GRS_VOL":{
		name:"Gross Vol",
		data_type:"n",
		control_type:"n",
	},
	"VOL_UOM":{
		name:"Volume Unit",
		data_type:"n",
		control_type:"l",
		list: "VolUOM",
	},
	"GRS_MASS":{
		name:"Gross Mass",
		data_type:"n",
		control_type:"n",
	},
	"MASS_UOM":{
		name:"Mass Unit",
		data_type:"n",
		control_type:"l",
		list: "MassUOM",
	},
	"COMMENT":{
		name:"Comment",
		data_type:"t",
		control_type:"t",
	},
}