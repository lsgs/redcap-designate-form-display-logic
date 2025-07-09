<?php
/**
 * Designate Form Display Logic
 * View and edit Form Display Logic rules by Event and Instrument via the Designate Instruments to My Events page
 * @author Luke Stevens, Murdoch Children's Research Institute
 */
namespace MCRI\DesignateFDL;

use ExternalModules\AbstractExternalModule;

class DesignateFDL extends AbstractExternalModule
{
    protected $arm;
    protected $arm_events_forms;

    public function redcap_every_page_top($project_id) {
        if (empty($project_id)) return;
        if (!defined('PAGE') || PAGE!=='Design/designate_forms.php') return;
        global $Proj;
        if (!$Proj->project['repeatforms']) return; // module does nothing in non-longitudinal projects
        // if (!\FormDisplayLogic::FormDisplayLogicEnabled($project_id)) return; // do nothing if no FDL

        $event_unique_names = \REDCap::getEventNames(true, false);

        $fdl_conditions = \FormDisplayLogic::getFormDisplayLogicTableValues($project_id);

        $this->arm = (isset($_GET['arm_id']) && intval($_GET['arm_id'])>0) ? intval($_GET['arm_id']) : 1;
        $this->arm_events_forms = $Proj->getInstrEventMapRecords(['arms'=>$this->arm]);

        // get this arm's events and forms, and the FDL conditions pertaining to each event/form
        $arm_events = $Proj->getEventsByArmNum($this->arm);
        foreach ($arm_events as $event_id) {
            $event_unique_name = $event_unique_names[$event_id];
            foreach ($this->arm_events_forms as $i => $event_form) {
                if ($event_form['unique_event_name']==$event_unique_name) {
                    $this->arm_events_forms[$i]['event_id'] = $event_id;
                }
            }
        }

        // iterate the fdl control conditions and where relevant add to form/event 
        foreach ($this->arm_events_forms as $i => $event_form) {
            $this->arm_events_forms[$i]['conditions'] = array();
            foreach ($fdl_conditions['controls'] as $control) {
                foreach ($control['form-name'] as $form_dash_event) {
                    list($control_form,$control_event) = explode('-', $form_dash_event);
                    $this_event = $event_form['event_id'];
                    $this_form = $event_form['form'];

                    // rule applies to form either if not event specific or event matches 
                    if ($control_form==$this_form && ($control_event=='' || $control_event==$this_event) ) {
                        $this->arm_events_forms[$i]['conditions'][] = $control;
                    }
                }
            }
        }

        $this->initializeJavascriptModuleObject();
        ?>
        <script type="text/javascript">
            // Designate Form Display Logic
            $(function(){
                var module = <?=$this->getJavascriptModuleObjectName()?>;
                module.tt_add('fdl','<?=\RCView::tt_js('design_985')?>');
                module.tt_add('view','<?=\RCView::tt_js('global_84')?>');
                module.event_unique_names = <?=\json_encode_rc($event_unique_names)?>;
                module.arm_events_forms = <?=\json_encode_rc($this->arm_events_forms)?>;
                module.form_event_popover = '<a tabindex="0" class="" role="button" data-bs-toggle="popover" data-bs-placement="bottom"></a>'
                module.getTickFormEvent = function(img) {
                    let id = $(img).attr('id'); // e.g. 'img--screening_form--3210
                    let td = $(img).parents('td:first');
                    let i = $(td).index();
                    let th = $('#event_grid_table th').eq(i);
                    let idparts = id.split('--');
                    let form_name = idparts[1];
                    let event_id = idparts[2];
                    let form_label = $(td).parent('tr').find('td:first').text();
                    let event_label = $(th).find('div:first').text();
                    return {
                        form_name: form_name,
                        form_label: form_label,
                        event_id: event_id,
                        event_name: module.event_unique_names[event_id],
                        event_label: event_label
                    };
                };
                module.getTickProperty = function(img, prop) {
                    let tick = module.getTickFormEvent(img);
                    return (tick.hasOwnProperty(prop)) ? tick[prop] : null;
                };
                module.getConditions = function(event_name, form_name){
                    let conditions = [];
                    module.arm_events_forms.forEach(function(e){
                        if (e.unique_event_name == event_name && e.form == form_name && e.conditions.length) {
                            e.conditions.forEach(function(c){
                                conditions.push(c.control_id);
                            });
                        }
                    });
                    return conditions;
                };
                module.popoverContent = function(el) {
                    let tick = $(el).find('img:first');
                    let event_name = module.getTickProperty(tick, 'event_name');
                    let form_name = module.getTickProperty(tick, 'form_name');
                    
                    let conditions = module.getConditions(event_name, form_name);
                    let conditionText = '<b>'+conditions.length+'</b> condition'+((conditions.length===1)?'':'s');

                    let content = '<b>'+module.getTickProperty(tick, 'event_label')+'</b>';
                    content += '<br>'+module.getTickProperty(tick, 'form_label');
                    content += '<br>'+conditionText;
                    return content;
                }
                module.init = function() {
                    console.log(module.arm_events_forms);
                    $('#event_grid_table img').wrap(function(){
                        // wrap each form/event tick in a <a> with popover
                        return module.form_event_popover;
                    });
                    const popoverTriggerList = $('a[data-bs-toggle="popover"]');
                    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, {
                        trigger: 'focus',
                        html: true,
                        sanitize: false,
                        title: '<i class="fas fa-eye-slash"></i> '+module.tt('fdl'), // Form Display Logic
                        content: module.popoverContent(popoverTriggerEl)
                    }));
                    $('a.DesignateFDL_view').on('click',function(){
                        console.log(this);
                    });
                };

                $(document).ready(function(){ module.init(); });
            });
        </script>
        <?php
    }
}