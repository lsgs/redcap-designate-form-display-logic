********************************************************************************
# Focused Form Display Logic

Luke Stevens, Murdoch Children's Research Institute https://www.mcri.edu.au

[https://github.com/lsgs/redcap-designate-form-display-logic/](https://github.com/lsgs/redcap-designate-form-display-logic/)

********************************************************************************
## Summary

View and edit Form Display Logic rules using a focused context:
- by Instrument in the Online Designer table of instruments
- by Event and Instrument on the Designate Instruments to My Events page

## Configuration

There are no module-specific configuration settings for this external module.

## Operation

The module operates on the "Online Designer" instrument list and "Designate Instruments to My Events" pages only. When enabled on a project, each instrument or designated event/form pair will be augmented with the Form Display Logic "eye-with-slash" icon.

The added icon provides the following behaviours:
- Light grey colour indicates there are currently no FDL controls applicable to the event/form.
- Dark grey colour indicates there is one or more FDL control applicable to the event/form.
- A tooltip showing event and form labels, and the number of FDL controls applicable to the event/form (see screenshot below).
- Click the icon to launch the Form Display Logic editor dialog:
    - The "Optional Settings" section is hidden.
    - The "Delete all conditions" button is hidden.
    - Only conditions applicable to the event/form are shown. All other conditions are hidden.
    - Adding new conditions is allowed.

## Screenshots

### Online Designer Instrument List

<img alt="Screenshot showing Form Display Logic icons on Online Designer instrument list page." src="https://redcap.link/instrument-fdl" />

### Designate Instruments to My Events

<img alt="Screenshot showing Form Display Logic icons on Designate Instruments page." src="https://redcap.link/designate-fdl" />

********************************************************************************