/**
 * Created by JetBrains PhpStorm.
 * User: ernesto
 * Date: 6/27/11
 * Time: 8:43 AM
 * To change this template use File | Settings | File Templates.
 *
 *
 * @namespace Patient.patientLiveSearch
 */
Ext.define('App.classes.combo.LabObservations', {
	extend       : 'Ext.form.ComboBox',
	alias        : 'widget.labobservationscombo',
	initComponent: function() {
		var me = this;

		Ext.define('labObservationsComboModel', {
			extend: 'Ext.data.Model',
			fields: [
              		{name: 'label' },
              		{name: 'name' },
              		{name: 'unit' },
              		{name: 'range_start' },
              		{name: 'range_end' },
              		{name: 'threshold' },
              		{name: 'notes' }
			],
			proxy : {
				type  : 'direct',
				api   : {
					read: Services.getAllLabObservations
				}
			}
		});

		me.store = Ext.create('Ext.data.Store', {
			model   : 'labObservationsComboModel',
			autoLoad: false
		});

		Ext.apply(this, {
			store       : me.store,
			displayField: 'label',
			valueField  : 'id',
			emptyText   : 'Select Existing Observation',
            editable    : false,
            width: 810,
			listConfig  : {
				getInnerTpl: function() {
					return '<div>' +
                        '<span style="width:200px;display:inline-block;"><span style="font-weight:bold;">Label:</span> {label},</span>' +
                        '<span style="width:90px;display:inline-block;"><span style="font-weight:bold;">Unit:</span> {unit},</span>' +
                        '<span style="width:150px;display:inline-block;"><span style="font-weight:bold;">Range Start:</span> {range_start},</span>' +
                        '<span style="width:130px;display:inline-block;"><span style="font-weight:bold;">Range End:</span> {range_end},</span>' +
                        '<span style="width:100px;display:inline-block;"><span style="font-weight:bold;">Threshold:</span> {threshold}</span>' +
                        '</div>';
				}
			}
		}, null);

		me.callParent();
	}

});