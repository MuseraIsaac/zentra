/**
 * ---------------------------------------------------------------------
 *
 * ZENTRA - Gestionnaire Libre de Parc Informatique
 *
 * http://zentra-project.org
 *
 * @copyright 2015-2026 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of ZENTRA.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

/* eslint prefer-arrow-callback: 0 */
/* eslint prefer-template: 0 */
/* eslint no-var: 0 */

// Load cytoscape
var cytoscape = window.cytoscape;

// Needed for JS lint validation
/* global _ */
/* global hexToRgb */
/* global contrast */

var ZENTRAImpact = {

    // Constants to represent nodes and edges
    NODE: 1,
    EDGE: 2,

    // Constants for graph direction (bitmask)
    DEFAULT : 0,   // 0b00
    FORWARD : 1,   // 0b01
    BACKWARD: 2,   // 0b10
    BOTH    : 3,   // 0b11

    // Constants for graph edition mode
    EDITION_DEFAULT     : 1,
    EDITION_ADD_NODE    : 2,
    EDITION_ADD_EDGE    : 3,
    EDITION_DELETE      : 4,
    EDITION_ADD_COMPOUND: 5,
    EDITION_SETTINGS    : 6,

    // Constants for ID separator
    NODE_ID_SEPERATOR: "::",
    EDGE_ID_SEPERATOR: "->",

    // Constants for delta action
    DELTA_ACTION_ADD   : 1,
    DELTA_ACTION_UPDATE: 2,
    DELTA_ACTION_DELETE: 3,

    // Constants for action stack
    ACTION_MOVE                         : 1,
    ACTION_ADD_NODE                     : 2,
    ACTION_ADD_EDGE                     : 3,
    ACTION_ADD_COMPOUND                 : 4,
    ACTION_ADD_GRAPH                    : 5,
    ACTION_EDIT_COMPOUND                : 6,
    ACTION_REMOVE_FROM_COMPOUND         : 7,
    ACTION_DELETE                       : 8,
    ACTION_EDIT_MAX_DEPTH               : 9,
    ACTION_EDIT_IMPACT_VISIBILITY       : 10,
    ACTION_EDIT_DEPENDS_VISIBILITY      : 11,
    ACTION_EDIT_DEPENDS_COLOR           : 12,
    ACTION_EDIT_IMPACT_COLOR            : 13,
    ACTION_EDIT_IMPACT_AND_DEPENDS_COLOR: 14,
    ACTION_EDIT_EDGE                    : 15,
    // Constans for depth
    DEFAULT_DEPTH: 5,
    MAX_DEPTH: 10,
    NO_DEPTH_LIMIT: 10000,

    // Store the initial state of the graph
    initialState: null,

    // Store the visibility settings of the different direction of the graph
    directionVisibility: {},

    // Store defaults colors for edge
    defaultColors: {},

    // Store color for egdes
    edgeColors: {},

    // Cytoscape instance
    cy: null,

    // The impact network container
    impactContainer: null,

    // The graph edition mode
    editionMode: null,

    // Start node of the graph (id)
    startNode: null,

    // Maximum depth of the graph (default 5)
    maxDepth: this.DEFAULT_DEPTH,

    // Is the graph readonly ?
    readonly: true,

    // Fullscreen
    fullscreen: false,

    // Used in add assets sidebar
    selectedItemtype: "",
    addAssetPage: 0,

    // Action stack for undo/redo
    undoStack: [],
    redoStack: [],

    // Buffer used when generating positions for unset nodes
    no_positions: [],

    // Register badges hitbox so they can be clicked
    badgesHitboxes: [],

    // Store selectors
    selectors: {
        // Dialogs
        ongoingDialog           : "#ongoing_dialog",
        ongoingDialogBody       : "#ongoing_dialog .modal-body",
        editCompoundDialog      : "#edit_compound_dialog",
        editCompoundDialogSave  : '#edit_compound_save',
        editCompoundDialogCancel: '#edit_compound_cancel',
        editEdgeDialog          : "#edit_edge_dialog",
        editEdgeDialogSave      : '#edit_edge_save',
        editEdgeDialogCancel    : '#edit_edge_cancel',

        // Inputs
        compoundName         : "input[name=compound_name]",
        compoundColor        : "input[name=compound_color]",
        edgeName             : "input[name=edge_name]",
        dependsColor         : "input[name=depends_color]",
        impactColor          : "input[name=impact_color]",
        impactAndDependsColor: "input[name=impact_and_depends_color]",
        toggleImpact         : "#toggle_impact",
        toggleDepends        : "#toggle_depends",
        maxDepth             : "#max_depth",
        maxDepthView         : "#max_depth_view",

        // Toolbar
        helpText        : "#help_text",
        save            : "#save_impact",
        addNode         : "#add_node",
        addEdge         : "#add_edge",
        addCompound     : "#add_compound",
        deleteElement   : "#delete_element",
        export          : "#export_graph",
        expandToolbar   : "#expand_toolbar",
        toggleFullscreen: "#toggle_fullscreen",
        impactSettings  : "#impact_settings",
        sideToggle      : ".impact-side-toggle",
        sideToggleIcon  : ".impact-side-toggle i",
        undo            : "#impact_undo",
        redo            : "#impact_redo",

        // Sidebar content
        side                    : ".impact-side",
        sidePanel               : ".impact-side-panel",
        sideAddNode             : ".impact-side-add-node",
        sideSettings            : ".impact-side-settings",
        sideSearch              : ".impact-side-search",
        sideSearchSpinner       : ".impact-side-search-spinner",
        sideSearchNoResults     : ".impact-side-search-no-results",
        sideSearchMore          : ".impact-side-search-more",
        sideSearchResults       : ".impact-side-search-results",
        sideSearchSelectItemtype: ".impact-side-select-itemtype",
        sideSearchFilterItemtype: "#impact-side-filter-itemtypes",
        sideFilterAssets        : "#impact-side-filter-assets",
        sideFilterItem          : ".impact-side-filter-itemtypes-item",

        // Others
        form       : "form[name=form_impact_network]",
        dropPreview: ".impact-drop-preview",
    },

    // Data that needs to be stored/shared between events
    eventData: {
        addEdgeStart       : null,        // Store starting node of a new edge
        tmpEles            : null,        // Temporary collection used when adding an edge
        lastClicktimestamp : null,        // Store last click timestamp
        lastClickTarget    : null,        // Store last click target
        boxSelected        : [],
        grabNodeStart      : null,
        boundingBox        : null,
        showPointerForBadge: false,
        previousCursor     : "default",
        ctrlDown           : false,
        editCompound       : null,        // Compound being edited
        editEdge           : null,        // Edge being edited
    },

    /**
    * Add given action to undo stack and reset redo stack
    * @param {Number} action_code const ACTION_XXXX
    * @param {Object} data        data specific to the action
    */
    addToUndo : function(action_code, data) {
        // Add new item to undo list
        this.undoStack.push({
            code: action_code,
            data: data
        });
        $(this.selectors.undo).removeClass("impact-disabled");

        // Clear redo list
        this.redoStack = [];
        $(this.selectors.redo).addClass("impact-disabled");
    },

    /**
    * Undo last action
    */
    undo: function() {
        // Empty stack, stop here
        if (this.undoStack.length === 0) {
            return;
        }

        var action = this.undoStack.pop();
        var data = action.data;

        // Add action to redo stack
        this.redoStack.push(action);
        $(this.selectors.redo).removeClass("impact-disabled");

        switch (action.code) {
            // Set node to old position
            // Available data: node, oldPosition, newPosition and newParent
            case this.ACTION_MOVE:
                this.cy.filter("node" + this.makeIDSelector(data.node))
                    .position({
                        x: data.oldPosition.x,
                        y: data.oldPosition.y,
                    });

                if (data.newParent !== null) {
                    this.cy.filter("node" + this.makeIDSelector(data.node))
                        .move({parent: null});
                }
                break;

                // Remove node
                // Available data: toAdd
            case this.ACTION_ADD_NODE:
                this.cy.getElementById(data.toAdd.data.id).remove();
                break;

                // Delete edge
                // Available data; id, data
            case this.ACTION_ADD_EDGE:
                this.cy.remove("edge" + this.makeIDSelector(data.id));
                this.updateFlags();
                break;

                // Delete compound
                // Available data: data, children
            case this.ACTION_ADD_COMPOUND:
                data.children.forEach(function(id) {
                    ZENTRAImpact.cy.filter("node" + ZENTRAImpact.makeIDSelector(id))
                        .move({parent: null});
                });
                this.cy.remove("node" + this.makeIDSelector(data.data.id));
                this.updateFlags();
                break;

                // Remove the newly added graph
                // Available data: edges, nodes, compounds
            case this.ACTION_ADD_GRAPH:
            // Delete edges
                data.edges.forEach(function(edge) {
                    ZENTRAImpact.cy.getElementById(edge.id).remove();
                });

                // Delete compounds
                data.compounds.forEach(function(compound) {
                    compound.compoundChildren.forEach(function(nodeId) {
                        ZENTRAImpact.cy.getElementById(nodeId).move({
                            parent: null
                        });
                    });

                    ZENTRAImpact.cy.getElementById(compound.compoundData.id).remove();
                });

                // Delete nodes
                data.nodes.forEach(function(node) {
                    ZENTRAImpact.cy.getElementById(node.nodeData.id).remove();
                });

                this.updateFlags();

                break;

                // Revert edit
                // Available data: id, label, color, oldLabel, oldColor
            case this.ACTION_EDIT_COMPOUND:
                this.cy.filter("node" + this.makeIDSelector(data.id)).data({
                    label: data.oldLabel,
                    color: data.oldColor,
                });
                ZENTRAImpact.cy.trigger("change");
                break;

            // Revert edit
            // Available data: id, label, oldLabel
            case this.ACTION_EDIT_EDGE:
                this.cy.filter("node" + this.makeIDSelector(data.id)).data({
                    label: data.oldLabel,
                });
                ZENTRAImpact.cy.trigger("change");
                break;

                // Re-add node to the compound (and recreate it needed)
                // Available data: nodeData, compoundData, children
            case this.ACTION_REMOVE_FROM_COMPOUND:
                if (data.children.length <= 2) {
                    // Recreate the compound and re-add every nodes
                    this.cy.add({
                        group: "nodes",
                        data: data.compoundData,
                    });

                    data.children.forEach(function(childId) {
                        ZENTRAImpact.cy.getElementById(childId)
                            .move({parent: data.compoundData.id});
                    });
                } else {
                    // Add the node that was removed
                    this.cy.getElementById(data.nodeData.id)
                        .move({parent: data.compoundData.id});
                }

                break;

                // Re-add given nodes, edges and compounds
                // Available data: nodes, edges, compounds
            case this.ACTION_DELETE:
            // Add nodes
                data.nodes.forEach(function(node) {
                    var newNode = ZENTRAImpact.cy.add({
                        group: "nodes",
                        data: node.nodeData,
                    });
                    newNode.position(node.nodePosition);
                });

                // Add compound
                data.compounds.forEach(function(compound) {
                    ZENTRAImpact.cy.add({
                        group: "nodes",
                        data: compound.compoundData,
                    });

                    compound.compoundChildren.forEach(function(nodeId) {
                        ZENTRAImpact.cy.getElementById(nodeId).move({
                            parent: compound.compoundData.id
                        });
                    });
                });

                // Add edges
                data.edges.forEach(function(edge) {
                    ZENTRAImpact.cy.add({
                        group: "edges",
                        data: edge,
                    });
                });

                this.updateFlags();

                break;

                // Toggle impact visibility
            case this.ACTION_EDIT_IMPACT_VISIBILITY:
                this.toggleVisibility(this.FORWARD);
                $(ZENTRAImpact.selectors.toggleImpact).prop(
                    'checked',
                    !$(ZENTRAImpact.selectors.toggleImpact).prop('checked')
                );
                break;

                // Toggle depends visibility
            case this.ACTION_EDIT_DEPENDS_VISIBILITY:
                this.toggleVisibility(this.BACKWARD);
                $(ZENTRAImpact.selectors.toggleDepends).prop(
                    'checked',
                    !$(ZENTRAImpact.selectors.toggleDepends).prop('checked')
                );
                break;

                // Set previous value for "depends" color
                // Available data: oldColor, newColor
            case this.ACTION_EDIT_DEPENDS_COLOR:
                this.setEdgeColors({
                    backward: data.oldColor,
                });
                $(ZENTRAImpact.selectors.dependsColor).val(
                    ZENTRAImpact.edgeColors[ZENTRAImpact.BACKWARD]
                );
                this.updateStyle();
                this.cy.trigger("change");
                break;

                // Set previous value for "impact" color
                // Available data: oldColor, newColor
            case this.ACTION_EDIT_IMPACT_COLOR:
                this.setEdgeColors({
                    forward: data.oldColor,
                });
                $(ZENTRAImpact.selectors.impactColor).val(
                    ZENTRAImpact.edgeColors[ZENTRAImpact.FORWARD]
                );
                this.updateStyle();
                this.cy.trigger("change");
                break;

                // Set previous value for "impact and depends" color
                // Available data: oldColor, newColor
            case this.ACTION_EDIT_IMPACT_AND_DEPENDS_COLOR:
                this.setEdgeColors({
                    both: data.oldColor,
                });
                $(ZENTRAImpact.selectors.impactAndDependsColor).val(
                    ZENTRAImpact.edgeColors[ZENTRAImpact.BOTH]
                );
                this.updateStyle();
                this.cy.trigger("change");
                break;

                // Set previous value for max depth
                // Available data: oldDepth, newDepth
            case this.ACTION_EDIT_MAX_DEPTH:
                this.setDepth(data.oldDepth);
                $(ZENTRAImpact.selectors.maxDepth).val(data.oldDepth);
                break;
        }

        if (this.undoStack.length === 0) {
            $(this.selectors.undo).addClass("impact-disabled");
        }

    },

    /**
    * Redo last undoed action
    */
    redo: function() {
        // Empty stack, stop here
        if (this.redoStack.length === 0) {
            return;
        }

        var action = this.redoStack.pop();
        var data = action.data;

        // Add action to undo stack
        this.undoStack.push(action);
        $(this.selectors.undo).removeClass("impact-disabled");

        switch (action.code) {
            // Set node to new position
            // Available data: node, oldPosition, newPosition and newParent
            case this.ACTION_MOVE:
                this.cy.filter("node" + this.makeIDSelector(data.node))
                    .position({
                        x: data.newPosition.x,
                        y: data.newPosition.y,
                    });

                if (data.newParent !== null) {
                    this.cy.filter("node" + this.makeIDSelector(data.node))
                        .move({parent: data.newParent});
                }
                break;

                // Add the node again
                // Available data: toAdd
            case this.ACTION_ADD_NODE:
                this.cy.add(data.toAdd);
                break;

                // Add edge
                // Available data; id, data
            case this.ACTION_ADD_EDGE:
                this.cy.add({
                    group: "edges",
                    data: data,
                });
                this.updateFlags();

                break;

                // Add compound and update its children
                // Available data: data, children
            case this.ACTION_ADD_COMPOUND:
                this.cy.add({
                    group: "nodes",
                    data: data.data,
                });
                data.children.forEach(function(id) {
                    ZENTRAImpact.cy.filter("node" + ZENTRAImpact.makeIDSelector(id))
                        .move({parent: data.data.id});
                });
                this.updateFlags();

                break;

                // Insert again the graph
                // Available data: edges, nodes, compounds
            case this.ACTION_ADD_GRAPH:
            // Add nodes
                data.nodes.forEach(function(node) {
                    var newNode = ZENTRAImpact.cy.add({
                        group: "nodes",
                        data: node.nodeData,
                    });
                    newNode.position(node.nodePosition);
                });

                // Add compound
                data.compounds.forEach(function(compound) {
                    ZENTRAImpact.cy.add({
                        group: "nodes",
                        data: compound.compoundData,
                    });

                    compound.compoundChildren.forEach(function(nodeId) {
                        ZENTRAImpact.cy.getElementById(nodeId).move({
                            parent: compound.compoundData.id
                        });
                    });
                });

                // Add edges
                data.edges.forEach(function(edge) {
                    ZENTRAImpact.cy.add({
                        group: "edges",
                        data: edge,
                    });
                });

                this.updateFlags();

                break;

                // Reapply edit
                // Available data : id, label, color, previousLabel, previousColor
            case this.ACTION_EDIT_COMPOUND:
                this.cy.filter("node" + this.makeIDSelector(data.id)).data({
                    label: data.label,
                    color: data.color,
                });
                ZENTRAImpact.cy.trigger("change");
                break;
                // Reapply edit
                // Available data : id, label, previousLabel
            case this.ACTION_EDIT_EDGE:
                this.cy.filter("node" + this.makeIDSelector(data.id)).data({
                    label: data.label,
                });
                ZENTRAImpact.cy.trigger("change");
                break;
                // Remove node from the compound (and delete if needed)
                // Available data: nodeData, compoundData, children
            case this.ACTION_REMOVE_FROM_COMPOUND:
                if (data.children.length <= 2) {
                    // Remove every nodes and delete the compound
                    data.children.forEach(function(childId) {
                        ZENTRAImpact.cy.getElementById(childId)
                            .move({parent: null});
                    });

                    this.cy.getElementById(data.compoundData.id).remove();
                } else {
                    // Remove only he node that was re-added
                    this.cy.getElementById(data.nodeData.id)
                        .move({parent: null});
                }

                break;

                // Re-delete given nodes, edges and compounds
                // Available data: nodes, edges, compounds
            case this.ACTION_DELETE:
            // Delete edges
                data.edges.forEach(function(edge) {
                    ZENTRAImpact.cy.getElementById(edge.id).remove();
                });

                // Delete compounds
                data.compounds.forEach(function(compound) {
                    compound.compoundChildren.forEach(function(nodeId) {
                        ZENTRAImpact.cy.getElementById(nodeId).move({
                            parent: null
                        });
                    });

                    ZENTRAImpact.cy.getElementById(compound.compoundData.id).remove();
                });

                // Delete nodes
                data.nodes.forEach(function(node) {
                    ZENTRAImpact.cy.getElementById(node.id).remove();
                });

                this.updateFlags();

                break;

                // Toggle impact visibility
            case this.ACTION_EDIT_IMPACT_VISIBILITY:
                this.toggleVisibility(this.FORWARD);
                $(ZENTRAImpact.selectors.toggleImpact).prop(
                    'checked',
                    !$(ZENTRAImpact.selectors.toggleImpact).prop('checked')
                );
                break;

                // Toggle depends visibility
            case this.ACTION_EDIT_DEPENDS_VISIBILITY:
                this.toggleVisibility(this.BACKWARD);
                $(ZENTRAImpact.selectors.toggleDepends).prop(
                    'checked',
                    !$(ZENTRAImpact.selectors.toggleDepends).prop('checked')
                );
                break;

                // Set new value for "depends" color
                // Available data: oldColor, newColor
            case this.ACTION_EDIT_DEPENDS_COLOR:
                this.setEdgeColors({
                    backward: data.newColor,
                });
                $(ZENTRAImpact.selectors.dependsColor).val(
                    ZENTRAImpact.edgeColors[ZENTRAImpact.BACKWARD]
                );
                this.updateStyle();
                this.cy.trigger("change");
                break;

                // Set new value for "impact" color
                // Available data: oldColor, newColor
            case this.ACTION_EDIT_IMPACT_COLOR:
                this.setEdgeColors({
                    forward: data.newColor,
                });
                $(ZENTRAImpact.selectors.forwardColor).val(
                    "set",
                    ZENTRAImpact.edgeColors[ZENTRAImpact.FORWARD]
                );
                this.updateStyle();
                this.cy.trigger("change");
                break;

                // Set new value for "impact and depends" color
                // Available data: oldColor, newColor
            case this.ACTION_EDIT_IMPACT_AND_DEPENDS_COLOR:
                this.setEdgeColors({
                    both: data.newColor,
                });
                $(ZENTRAImpact.selectors.impactAndDependsColor).val(
                    ZENTRAImpact.edgeColors[ZENTRAImpact.BOTH]
                );
                this.updateStyle();
                this.cy.trigger("change");
                break;

                // Set new value for max depth
                // Available data: oldDepth, newDepth
            case this.ACTION_EDIT_MAX_DEPTH:
                this.setDepth(data.newDepth);
                $(ZENTRAImpact.selectors.maxDepth).val(data.newDepth);
                break;
        }

        if (this.redoStack.length === 0) {
            $(this.selectors.redo).addClass("impact-disabled");
        }
    },

    /**
    * Selector for nodes to hide according to depth and flag settings
    */
    getHiddenSelector: function() {
        var depthSelector = '[depth > ' + this.maxDepth + '][depth !> ' + Number.MAX_SAFE_INTEGER + ']';
        var flagSelector;

        // We have to compute the flags ourselves as bit comparison operators are
        // not supported by cytoscape selectors
        var forward = this.directionVisibility[this.FORWARD];
        var backward = this.directionVisibility[this.BACKWARD];

        if (forward && backward) {
            // Hide nothing
            flagSelector = "[flag = -1]";
        } else if (forward && !backward) {
            // Hide backward
            flagSelector = "[flag = " + this.BACKWARD + "]";
        } else if (!forward && backward) {
            // Hide forward
            flagSelector = "[flag = " + this.FORWARD + "]";
        } else {
            // Hide all but start node and not connected nodes
            flagSelector = '[flag != 0]';
        }

        return flagSelector + ', ' + depthSelector;
    },

    /**
    * Get network style
    *
    * @returns {Array}
    */
    getNetworkStyle: function() {
        let body_text_color = $(document.body).css("--tblr-body-color");
        // If body color is somehow invalid, default to black
        if (!body_text_color || body_text_color === "") {
            body_text_color = "#000000";
        }
        return [
            {
                selector: 'core',
                style: {
                    'selection-box-opacity'     : '0.2',
                    'selection-box-border-width': '0',
                    'selection-box-color'       : '#24acdf'
                }
            },
            {
                selector: 'node',
                style: {
                    color: body_text_color
                }
            },
            {
                selector: 'node:parent',
                style: {
                    'padding'           : '30px',
                    'shape'             : 'roundrectangle',
                    'border-width'      : '1px',
                    'background-opacity': '0.5',
                    'font-size'         : '1.1em',
                    'background-color'  : '#d2d2d2',
                    'text-margin-y'     : '20px',
                    'text-opacity'      : 0.7,
                }
            },
            {
                selector: 'node:parent[label]',
                style: {
                    'label': 'data(label)',
                }
            },
            {
                selector: 'node:parent[color]',
                style: {
                    'border-color'      : 'data(color)',
                    'background-color'  : 'data(color)',
                }
            },
            {
                selector: 'node[image]',
                style: {
                    'label'             : 'data(label)',
                    'shape'             : 'rectangle',
                    'background-color'  : '#666',
                    'background-image'  : 'data(image)',
                    'background-fit'    : 'contain',
                    'background-opacity': '0',
                    'font-size'         : '1em',
                    'text-opacity'      : 0.7,
                    'overlay-opacity'   : 0.01,
                    'overlay-color'     : "white",
                }
            },
            {
                selector: 'node[highlight=1]',
                style: {
                    'font-weight': 'bold',
                }
            },
            {
                selector: ':selected',
                style: {
                    'overlay-opacity': 0.2,
                    'overlay-color'  : "gray",
                }
            },
            {
                selector: '[todelete=1]:selected',
                style: {
                    'overlay-opacity': 0.2,
                    'overlay-color': 'red',
                }
            },
            {
                selector: ZENTRAImpact.getHiddenSelector(),
                style: {
                    'display': 'none',
                }
            },
            {
                selector: '[id="tmp_node"]',
                style: {
                    // Use opacity instead of display none here as this will make
                    // the edges connected to this node still visible
                    'opacity': 0,
                }
            },
            {
                selector: 'edge',
                style: {
                    'width'                    : 1,
                    'line-color'               : this.edgeColors[0],
                    'target-arrow-color'       : this.edgeColors[0],
                    'target-arrow-shape'       : 'triangle',
                    'arrow-scale'              : 0.7,
                    'curve-style'              : 'bezier',
                    'source-endpoint'          : 'outside-to-node-or-label',
                    'target-endpoint'          : 'outside-to-node-or-label',
                    'source-distance-from-node': '2px',
                    'target-distance-from-node': '2px',
                }
            },
            {
                selector: 'edge[target="tmp_node"]',
                style: {
                    // We want the arrow to go exactly where the cursor of the user
                    // is on the graph, no padding.
                    'source-endpoint'          : 'inside-to-node',
                    'target-endpoint'          : 'inside-to-node',
                    'source-distance-from-node': '0px',
                    'target-distance-from-node': '0px',
                }
            },
            {
                selector: 'edge[label]',
                css: {
                    'label'        : 'data(label)',
                    'text-rotation': 'autorotate',
                    'text-margin-x': '0px',
                    'text-margin-y': '-10px',
                    'font-size'    : '0.8em'
                }
            },
            {
                selector: '[flag=' + ZENTRAImpact.FORWARD + ']',
                style: {
                    'line-color'        : this.edgeColors[ZENTRAImpact.FORWARD],
                    'target-arrow-color': this.edgeColors[ZENTRAImpact.FORWARD],
                }
            },
            {
                selector: '[flag=' + ZENTRAImpact.BACKWARD + ']',
                style: {
                    'line-color'        : this.edgeColors[ZENTRAImpact.BACKWARD],
                    'target-arrow-color': this.edgeColors[ZENTRAImpact.BACKWARD],
                }
            },
            {
                selector: '[flag=' + ZENTRAImpact.BOTH + ']',
                style: {
                    'line-color'        : this.edgeColors[ZENTRAImpact.BOTH],
                    'target-arrow-color': this.edgeColors[ZENTRAImpact.BOTH],
                }
            }
        ];
    },

    /**
    * Get network layout
    *
    * @returns {Object}
    */
    getPresetLayout: function (positions) {
        this.no_positions = [];

        return {
            name: 'preset',
            positions: function(node) {
                var x = 0;
                var y = 0;

                if (!node.isParent() && positions[node.data('id')] !== undefined) {
                    x = parseFloat(positions[node.data('id')].x);
                    y = parseFloat(positions[node.data('id')].y);
                } else if (!node.isParent()) {
                    // Add node to no_positions list if it doesn't have a saved position
                    ZENTRAImpact.no_positions.push(node);
                }

                return {
                    x: x,
                    y: y,
                };
            }
        };
    },

    /**
    * Generate postion for nodes that are not saved in the current context
    *
    * Firstly, order the positionless nodes in a way that the one that depends
    * on others positionless nodes are placed after their respective
    * dependencies
    *
    * Secondly, try to place each nodes on the graph:
    *    1) take a random non positionless neighbor of our node
    *    2) Find the closest node to this neighbor, save the distance (if this
    *    neighbor has no neighbor of its own use a set value for the distance)
    *    3) Try to place the node at the left or the right of the neighbor (
    *    depending on the edge direction, we want the graph to flow from left
    *    to right) at the saved position.
    *    4) If the position is not avaible, try at various angles bewteen -75°
    *    and 75°
    *    5) If the position is still not available, increase the distance and
    *    try again until a valid position is found
    */
    generateMissingPositions: function() {
        // Safety check, should not happen
        if (this.cy.filter("node:childless").length == this.no_positions.length) {
            // Set a random node as valid
            this.no_positions.pop();
        }

        // Keep tracks of the id of all the no yet placed nodes
        var not_placed = [];
        this.no_positions.forEach(function(node){
            not_placed.push(node.data('id'));
        });

        // First we need to order no_positions in a way that the ones that depend
        // on the positions of other nodes with no position are used last
        var clean_order = [];
        var np_valid = [];
        while (this.no_positions.length !== 0) {
            this.no_positions.forEach(function(node, index) {
            // Check that any neibhor is either valid (no in not placed) or has
            // just been validated (in np_valid)

                var valid = false;
                node.neighborhood().forEach(function(ele) {
                    if (valid) {
                        return;
                    }

                    // We don't need edges
                    if (!ele.isNode()) {
                        return;
                    }

                    if (not_placed.indexOf(ele.data('id')) === -1
                  || np_valid.indexOf(ele.data('id')) !== -1) {
                        valid = true;
                    }
                });

                if (valid) {
                    // Add to the list of validated nodes, set order and remove it
                    // from buffer
                    np_valid.push(node.data('id'));
                    clean_order.push(node);
                    // not_placed.splice(index, 1);
                    ZENTRAImpact.no_positions.splice(index, 1);
                }
            });
        }

        this.no_positions = clean_order;

        // Generate positions for nodes which lake them
        this.no_positions.forEach(function(node){
            // Find random neighbor with a valid position
            var neighbor = null;
            node.neighborhood().forEach(function(ele) {
            // We already found a valid neighor, skip until the end
                if (neighbor !== null) {
                    return;
                }

                if (!ele.isNode()) {
                    return;
                }

                // Ignore our starting node
                if (ele.data('id') == node.data('id')) {
                    return;
                }

                // Ignore node with no positions not yet placed
                if (not_placed.indexOf(ele.data('id')) !== -1) {
                    return;
                }

                // Valid neighor, let's pick it
                neighbor = ele;
            });

            // Should not happen if no_positions is correctly sorted
            if (neighbor === null) {
                return;
            }

            // We now need to find the closest node to the neighor
            var closest = null;
            var distance = Number.MAX_SAFE_INTEGER;
            neighbor.neighborhood().forEach(function(ele){
                if (!ele.isNode()) {
                    return;
                }

                var ele_distance = ZENTRAImpact.getDistance(neighbor.position(), ele.position());
                if (ele_distance < distance) {
                    distance = ele_distance;
                    closest = ele;
                }
            });

            // If our neighbor node has no neighors himself, use a set distance
            if (closest === null) {
                distance = 100;
            }

            // Find the edge between our node and the chosen neighbor
            var edge = node.edgesTo(neighbor)[0];
            if (edge == undefined) {
                edge = neighbor.edgesTo(node)[0];
            }

            // Set direction factor according to the edge direction (are we the
            // source or the target of this edge ?). This factor will be used to
            // know if the node must be placed before or after the neighbor
            var direction_factor;
            if (edge.data('target') == node.data('id')) {
                direction_factor = 1;
            } else {
                direction_factor = -1;
            }

            // Keep trying to place the node until we succeed$
            var success = false;
            while(!success) {
                var angle = 0;
                var angle_mirror = false;

                // Try all possible angles bewteen -75° and 75°
                while (angle !== -75) {
                    // Calculate the position
                    var position = {
                        x: direction_factor * (distance * Math.cos(angle * (Math.PI / 180))) + (neighbor.position().x),
                        y: distance * Math.sin(angle * (Math.PI / 180)) + neighbor.position().y,
                    };

                    // Check if position is available
                    var available = true;
                    ZENTRAImpact.cy.filter().forEach(function(ele){
                        var bdb = ele.boundingBox();
                        // var bdb = ele.renderedBoundingBox();

                        if ((bdb.x1 - 20) < position.x && (bdb.x2 + 20) > position.x
                     && (bdb.y1 - 20) < position.y && (bdb.y2 + 20) > position.y) {
                            available = false;
                        }
                    });

                    // Success, set the node position and go to the next one
                    if (available) {
                        node.position(position);
                        var np_index = not_placed.indexOf(node.data('id'));
                        not_placed.splice(np_index, 1);
                        success = true;
                        break;
                    }

                    if (!angle_mirror && angle !== 0) {
                        // We tried X°, lets try the "mirror angle" -X°]
                        angle = angle * -1;
                        angle_mirror = true;
                    } else {
                        // Add 15° and return to positive number
                        if (angle < 0) {
                            angle = 0 - angle;
                            angle_mirror = false;
                        }

                        angle += 15;
                    }
                }

                // Increase distance and try again
                distance += 30;
            }
        });

        // Reset buffer
        this.no_positions = [];
    },

    /**
    * Get network layout
    *
    * @returns {Object}
    */
    getDagreLayout: function () {
        return {
            name: 'dagre',
            rankDir: 'LR',
            fit: false
        };
    },

    /**
    * Get the current state of the graph
    *
    * @returns {Object}
    */
    getCurrentState: function() {
        var data = {edges: {}, compounds: {}, items: {}};

        // Load edges
        ZENTRAImpact.cy.edges().forEach(function(edge) {
            data.edges[edge.data('id')] = {
                name: edge.data('label'),
                source: edge.data('source'),
                target: edge.data('target'),
            };
        });

        // Load compounds
        ZENTRAImpact.cy.filter("node:parent").forEach(function(compound) {
            data.compounds[compound.data('id')] = {
                name: compound.data('label'),
                color: compound.data('color'),
            };
        });

        // Load items
        ZENTRAImpact.cy.filter("node:childless").forEach(function(node) {
            data.items[node.data('id')] = {
                impactitem_id: node.data('impactitem_id'),
                parent       : node.data('parent'),
                position     : node.position()
            };
        });

        return data;
    },

    /**
    * Delta computation for edges
    *
    * @returns {Object}
    */
    computeEdgeDelta: function(currentEdges) {
        var edgesDelta = {};

        // First iterate on the edges we had in the initial state
        Object.keys(ZENTRAImpact.initialState.edges).forEach(function(edgeID) {
            var edge = ZENTRAImpact.initialState.edges[edgeID];
            var source = edge.source.split(ZENTRAImpact.NODE_ID_SEPERATOR);
            var target = edge.target.split(ZENTRAImpact.NODE_ID_SEPERATOR);
            if (Object.prototype.hasOwnProperty.call(currentEdges, edgeID)) {
            // If the edge is still here in the current state, nothing happened
                var currentEdge = currentEdges[edgeID];

                // Check for updates ...
                if (edge.name != currentEdge.name) {
                    edgesDelta[edgeID] = {
                        action: ZENTRAImpact.DELTA_ACTION_UPDATE,
                        name  : currentEdge.name,
                        itemtype_source  : source[0],
                        items_id_source  : source[1],
                        itemtype_impacted: target[0],
                        items_id_impacted: target[1]
                    };
                }
                // Remove it from the currentEdges data so we can skip it later
                delete currentEdges[edgeID];
            } else {
            // If the edge is missing in the current state, it has been deleted
                edgesDelta[edgeID] = {
                    action           : ZENTRAImpact.DELTA_ACTION_DELETE,
                    itemtype_source  : source[0],
                    items_id_source  : source[1],
                    itemtype_impacted: target[0],
                    items_id_impacted: target[1]
                };
            }
        });

        // Now iterate on the edges we have in the current state
        // Since we removed the edges that were not modified in the previous step,
        // the remaining edges can only be new ones
        Object.keys(currentEdges).forEach(function (edgeID) {
            var edge = currentEdges[edgeID];
            var source = edge.source.split(ZENTRAImpact.NODE_ID_SEPERATOR);
            var target = edge.target.split(ZENTRAImpact.NODE_ID_SEPERATOR);
            edgesDelta[edgeID] = {
                action           : ZENTRAImpact.DELTA_ACTION_ADD,
                itemtype_source  : source[0],
                items_id_source  : source[1],
                itemtype_impacted: target[0],
                items_id_impacted: target[1]
            };
        });

        return edgesDelta;
    },

    /**
    * Delta computation for compounds
    *
    * @returns {Object}
    */
    computeCompoundsDelta: function(currentCompounds) {
        var compoundsDelta = {};

        // First iterate on the compounds we had in the initial state
        Object.keys(ZENTRAImpact.initialState.compounds).forEach(function(compoundID) {
            var compound = ZENTRAImpact.initialState.compounds[compoundID];
            if (Object.prototype.hasOwnProperty.call(currentCompounds, compoundID)) {
            // If the compound is still here in the current state
                var currentCompound = currentCompounds[compoundID];

                // Check for updates ...
                if (compound.name != currentCompound.name
               || compound.color != currentCompound.color) {
                    compoundsDelta[compoundID] = {
                        action: ZENTRAImpact.DELTA_ACTION_UPDATE,
                        name  : currentCompound.name,
                        color : currentCompound.color
                    };
                }

                // Remove it from the currentCompounds data
                delete currentCompounds[compoundID];
            } else {
            // If the compound is missing in the current state, it's been deleted
                compoundsDelta[compoundID] = {
                    action           : ZENTRAImpact.DELTA_ACTION_DELETE,
                };
            }
        });

        // Now iterate on the compounds we have in the current state
        Object.keys(currentCompounds).forEach(function (compoundID) {
            compoundsDelta[compoundID] = {
                action: ZENTRAImpact.DELTA_ACTION_ADD,
                name  : currentCompounds[compoundID].name,
                color : currentCompounds[compoundID].color
            };
        });

        return compoundsDelta;
    },

    /**
    * Delta computation for parents
    *
    * @returns {Object}
    */
    computeContext: function(currentNodes) {
        var positions = {};

        Object.keys(currentNodes).forEach(function (nodeID) {
            var node = currentNodes[nodeID];
            positions[nodeID] = {
                x: node.position.x,
                y: node.position.y
            };
        });

        return {
            node_id                 : this.startNode,
            positions               : JSON.stringify(positions),
            zoom                    : ZENTRAImpact.cy.zoom(),
            pan_x                   : ZENTRAImpact.cy.pan().x,
            pan_y                   : ZENTRAImpact.cy.pan().y,
            impact_color            : ZENTRAImpact.edgeColors[ZENTRAImpact.FORWARD],
            depends_color           : ZENTRAImpact.edgeColors[ZENTRAImpact.BACKWARD],
            impact_and_depends_color: ZENTRAImpact.edgeColors[ZENTRAImpact.BOTH],
            show_depends            : ZENTRAImpact.directionVisibility[ZENTRAImpact.BACKWARD],
            show_impact             : ZENTRAImpact.directionVisibility[ZENTRAImpact.FORWARD],
            max_depth               : ZENTRAImpact.maxDepth,
        };
    },

    /**
    * Delta computation for parents
    *
    * @returns {Object}
    */
    computeItemsDelta: function(currentNodes) {
        var itemsDelta = {};

        // Now iterate on the parents we have in the current state
        Object.keys(currentNodes).forEach(function (nodeID) {
            var node = currentNodes[nodeID];
            itemsDelta[node.impactitem_id] = {
                action   : ZENTRAImpact.DELTA_ACTION_UPDATE,
                parent_id: node.parent,
            };

            // Set parent to 0 if null
            if (node.parent == undefined) {
                node.parent = 0;
            }

            // Store parent
            itemsDelta[node.impactitem_id] = {
                action    : ZENTRAImpact.DELTA_ACTION_UPDATE,
                parent_id : node.parent,
            };
        });

        return itemsDelta;
    },

    /**
    * Compute the delta betwteen the initial state and the current state
    *
    * @returns {Object}
    */
    computeDelta: function () {
        // Store the delta for edges, compounds and parent
        var result = {};

        // Get the current state of the graph
        var currentState = this.getCurrentState();

        // Compute each deltas
        result.edges = this.computeEdgeDelta(currentState.edges);
        result.compounds = this.computeCompoundsDelta(currentState.compounds);
        result.items = this.computeItemsDelta(currentState.items);
        result.context = this.computeContext(currentState.items);

        return result;
    },

    /**
    * Get the context menu items
    *
    * @returns {Array}
    */
    getContextMenuItems: function(){
        return [
            {
                id             : 'goTo',
                content        : '<i class="ti ti-external-link me-2"></i>' + __("Go to"),
                tooltipText    : _.unescape(__("Open this element in a new tab")),
                selector       : 'node[link]',
                onClickFunction: this.menuOnGoTo
            },
            {
                id             : 'showOngoing',
                content        : '<i class="ti ti-alert-circle me-2"></i>' + __("Show ongoing tickets"),
                tooltipText    : _.unescape(__("Show ongoing tickets for this item")),
                selector       : 'node[hasITILObjects=1]',
                onClickFunction: this.menuOnShowOngoing
            },
            {
                id             : 'editEdge',
                content        : '<i class="ti ti-edit me-2"></i>' + __("Edge properties..."),
                tooltipText    : _.unescape(__("Set name for this edge")),
                selector       : 'edge',
                onClickFunction: this.menuOnEditEdge,
                show           : !this.readonly,
            },
            {
                id             : 'editCompound',
                content        : '<i class="ti ti-edit me-2"></i>' + __("Group properties..."),
                tooltipText    : _.unescape(__("Set name and/or color for this group")),
                selector       : 'node:parent',
                onClickFunction: this.menuOnEditCompound,
                show           : !this.readonly,
            },
            {
                id             : 'removeFromCompound',
                content        : '<i class="ti ti-home-move me-2"></i>' + __("Remove from group"),
                tooltipText    : _.unescape(__("Remove this asset from the group")),
                selector       : 'node:child',
                onClickFunction: this.menuOnRemoveFromCompound,
                show           : !this.readonly,
            },
            {
                id             : 'delete',
                content        : '<i class="ti ti-trash me-2"></i>' + __("Delete"),
                tooltipText    : _.unescape(__("Delete element")),
                selector       : 'node, edge',
                onClickFunction: this.menuOnDelete,
                show           : !this.readonly,
            },
        ];
    },

    addNode: function(itemID, itemType, position) {
        // Build a new graph from the selected node and insert it
        var node = {
            itemtype: itemType,
            items_id: itemID
        };
        var nodeID = ZENTRAImpact.makeID(ZENTRAImpact.NODE, node.itemtype, node.items_id);

        // Check if the node is already on the graph
        if (ZENTRAImpact.cy.filter('node[id="' + CSS.escape(nodeID) + '"]').length > 0) {
            alert(__('This asset already exists.'));
            return;
        }

        // Build the new subgraph
        $.when(ZENTRAImpact.buildGraphFromNode(node))
            .done(
                function (graph, params) {
                    // Insert the new graph data into the current graph
                    ZENTRAImpact.insertGraph(graph, params, {
                        id: nodeID,
                        x: position.x,
                        y: position.y
                    });
                    ZENTRAImpact.updateFlags();
                }
            ).fail(
                function () {
                    // Ajax failed
                    alert(__("Unexpected error."));
                }
            );
    },

    /**
    * Show the add node dialog
    */
    showOngoingDialog: function(ITILObjects) {
        $(ZENTRAImpact.selectors.ongoingDialogBody).html(
            ZENTRAImpact.buildOngoingDialogContent(ITILObjects)
        );
        $(ZENTRAImpact.selectors.ongoingDialog).modal('show');
    },

    /**
    * Set up event handlers for the edit compound dialog
    */
    prepareEditCompoundDialog: function() {
        $(this.selectors.editCompoundDialogSave).on('click', function() {
            var compound = ZENTRAImpact.eventData.editCompound.target;

            // Save compound name
            compound.data(
                'label',
                $(ZENTRAImpact.selectors.compoundName).val()
            );

            // Save compound color
            compound.data(
                'color',
                $(ZENTRAImpact.selectors.compoundColor).val()
            );

            // Close dialog
            $(ZENTRAImpact.selectors.editCompoundDialog).modal('hide');
            ZENTRAImpact.cy.trigger("change");

            // Log for undo
            if (ZENTRAImpact.eventData.newCompound == null) {
                var previousLabel = ZENTRAImpact.eventData.editCompound.previousLabel;
                var previousColor = ZENTRAImpact.eventData.editCompound.previousColor;

                ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_EDIT_COMPOUND, {
                    id      : compound.data('id'),
                    label   : compound.data('label'),
                    color   : compound.data('color'),
                    oldLabel: previousLabel,
                    oldColor: previousColor,
                });
            } else {
                var label = $(ZENTRAImpact.selectors.compoundName).val();
                var color = $(ZENTRAImpact.selectors.compoundColor).val();

                ZENTRAImpact.eventData.newCompound.data.label = label;
                ZENTRAImpact.eventData.newCompound.data.color = color;

                ZENTRAImpact.addToUndo(
                    ZENTRAImpact.ACTION_ADD_COMPOUND,
                    _.cloneDeep(ZENTRAImpact.eventData.newCompound)
                );

                ZENTRAImpact.eventData.newCompound = null;
            }
        });
    },

    /**
     * Set up event handlers for the edit edge dialog
     */
    prepareEditEdgeDialog: function() {
        $(this.selectors.editEdgeDialogSave).on('click', function() {
            var edge = ZENTRAImpact.eventData.editEdge.target;

            // Save edge name
            edge.data(
                'label',
                $(ZENTRAImpact.selectors.edgeName).val()
            );

            // Close dialog
            $(ZENTRAImpact.selectors.editEdgeDialog).modal('hide');
            ZENTRAImpact.cy.trigger("change");

            // Log for undo
            if (ZENTRAImpact.eventData.newEdge == null) {
                var previousLabel = ZENTRAImpact.eventData.editEdge.previousLabel;

                ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_EDIT_EDGE, {
                    id      : edge.data('id'),
                    label   : edge.data('label'),
                    oldLabel: previousLabel,
                });
            } else {
                var label = $(ZENTRAImpact.selectors.edgeName).val();

                ZENTRAImpact.eventData.newEdge.data.label = label;

                ZENTRAImpact.addToUndo(
                    ZENTRAImpact.ACTION_ADD_EDGE,
                    _.cloneDeep(ZENTRAImpact.eventData.newEdge)
                );

                ZENTRAImpact.eventData.newEdge = null;
            }
        });
    },

    /**
    * Show the edit compound dialog
    *
    * @param {Object} compound label, color
    */
    showEditCompoundDialog: function(compound) {
        var previousLabel = compound.data('label');
        var previousColor = compound.data('color');

        // Reset inputs
        $(ZENTRAImpact.selectors.compoundName).val(previousLabel);
        $(ZENTRAImpact.selectors.compoundColor).val(previousColor);

        // Set global event data
        this.eventData.editCompound = {
            target: compound,
            previousLabel: previousLabel,
            previousColor: previousColor,
        };

        // Show modal
        $(ZENTRAImpact.selectors.editCompoundDialog).modal('show');
    },

    /**
     * Show the edit edge dialog
     *
     * @param {Object} edge label
     */
    showEditEdgeDialog: function(edge) {
        var previousLabel = edge.data('label');

        // Reset inputs
        $(ZENTRAImpact.selectors.edgeName).val(previousLabel);

        // Set global event data
        this.eventData.editEdge = {
            target: edge,
            previousLabel: previousLabel
        };

        // Show modal
        $(ZENTRAImpact.selectors.editEdgeDialog).modal('show');
    },

    /**
    * Initialise variables
    *
    * @param {JQuery} impactContainer
    * @param {Object} colors properties: default, forward, backward, both
    * @param {string} startNode
    */
    prepareNetwork: function(
        impactContainer,
        colors,
        startNode
    ) {
        // Set container
        this.impactContainer = impactContainer;

        // Init directionVisibility
        this.directionVisibility[ZENTRAImpact.FORWARD] = true;
        this.directionVisibility[ZENTRAImpact.BACKWARD] = true;

        // Set colors for edges
        this.defaultColors = colors;
        this.setEdgeColors(colors);

        // Set start node
        this.startNode = startNode;

        // Init dialogs actions handlers
        this.prepareEditCompoundDialog();
        this.prepareEditEdgeDialog();

        this.initToolbar();
    },

    /**
    * Build the network graph
    *
    * @param {string} data (json)
    */
    buildNetwork: function(data, params, readonly) {
        var layout;

        // Init workspace status
        ZENTRAImpact.showDefaultWorkspaceStatus();

        // Load params - phase1 (before cytoscape creation)
        if (params.impactcontexts_id !== undefined && params.impactcontexts_id !== 0) {
            // Apply custom colors if defined
            this.setEdgeColors({
                forward : params.impact_color,
                backward: params.depends_color,
                both    : params.impact_and_depends_color,
            });

            // Apply max depth
            this.maxDepth = params.max_depth;

            // Preset layout based on node positions
            layout = this.getPresetLayout(JSON.parse(params.positions));
        } else {
            // Default params if no context was found
            this.setEdgeColors(this.defaultColors);
            this.maxDepth = this.DEFAULT_DEPTH;

            // Procedural layout
            layout = this.getDagreLayout();
        }

        // Init cytoscape
        this.cy = cytoscape({
            container: this.impactContainer,
            elements : data,
            style    : this.getNetworkStyle(),
            layout   : layout,
            wheelSensitivity: 0.25,
        });

        // If we used the preset layout, some nodes might lack positions
        this.generateMissingPositions();

        this.cy.minZoom(0.5);

        // Store initial data
        this.initialState = this.getCurrentState();

        // Enable editing if not readonly
        if (!readonly) {
            this.enableGraphEdition();
        }

        // Highlight starting node
        this.cy.filter("node[start]").data({
            highlight: 1,
            start_node: 1,
        });

        // Enable context menu
        this.cy.contextMenus({
            menuItems: this.getContextMenuItems(),
            menuItemClasses: [],
            contextMenuClasses: []
        });

        // Enable grid
        this.cy.gridGuide({
            gridStackOrder: 0,
            snapToGridOnRelease: false,
            snapToGridDuringDrag: true,
            gridSpacing: 12,
            drawGrid: true,
            panGrid: true,
            gridColor: getComputedStyle(document.documentElement).getPropertyValue('--tblr-border-color'),
        });

        // Disable box selection as we don't need it
        this.cy.boxSelectionEnabled(false);

        // Load params - phase 2 (after cytoscape creation)
        if (params.impactcontexts_id !== undefined && params.impactcontexts_id !== 0) {
            // Apply saved visibility
            if (!parseInt(params.show_depends)) {
                $(ZENTRAImpact.selectors.toggleImpact).prop("checked", false);
            }
            if (!parseInt(params.show_impact)) {
                $(ZENTRAImpact.selectors.toggleDepends).prop("checked", false);
            }
            this.updateFlags();

            // Set viewport
            if (params.zoom != '0') {
            // If viewport params are set, apply them
                this.cy.viewport({
                    zoom: parseFloat(params.zoom),
                    pan: {
                        x: parseFloat(params.pan_x),
                        y: parseFloat(params.pan_y),
                    }
                });

                // Check viewport is not empty or contains only one item
                var viewport = ZENTRAImpact.cy.extent();
                var empty = true;
                ZENTRAImpact.cy.nodes().forEach(function(node) {
                    if (node.position().x > viewport.x1
                  && node.position().x < viewport.x2
                  && node.position().y > viewport.x1
                  && node.position().y < viewport.x2
                    ){
                        empty = false;
                    }
                });

                if (empty || ZENTRAImpact.cy.filter("node:childless").length == 1) {
                    this.cy.fit();

                    if (this.cy.zoom() > 2.3) {
                        this.cy.zoom(2.3);
                        this.cy.center();
                    }
                }
            } else {
            // Else fit the graph and reduce zoom if needed
                this.cy.fit();

                if (this.cy.zoom() > 2.3) {
                    this.cy.zoom(2.3);
                    this.cy.center();
                }
            }
        } else {
            // Default params if no context was found
            this.cy.fit();

            if (this.cy.zoom() > 2.3) {
                this.cy.zoom(2.3);
                this.cy.center();
            }
        }

        // Register events handlers for cytoscape object
        this.cy.on('mousedown', 'node', this.nodeOnMousedown);
        this.cy.on('mouseup', this.onMouseUp);
        this.cy.on('mousemove', this.onMousemove);
        this.cy.on('mouseover', this.onMouseover);
        this.cy.on('mouseout', this.onMouseout);
        this.cy.on('click', this.onClick);
        this.cy.on('click', 'edge', this.edgeOnClick);
        this.cy.on('click', 'node', this.nodeOnClick);
        this.cy.on('box', this.onBox);
        this.cy.on('drag add remove change', this.onChange);
        this.cy.on('doubleClick', this.onDoubleClick);
        this.cy.on('remove', this.onRemove);
        this.cy.on('grabon', this.onGrabOn);
        this.cy.on('freeon', this.onFreeOn);
        this.initCanvasOverlay();

        // Global events
        $(document).keydown(this.onKeyDown);
        $(document).keyup(this.onKeyUp);

        // Enter EDITION_DEFAULT mode
        this.setEditionMode(ZENTRAImpact.EDITION_DEFAULT);

        // Init depth value
        var text = ZENTRAImpact.maxDepth;
        if (ZENTRAImpact.maxDepth >= ZENTRAImpact.MAX_DEPTH) {
            text = "infinity";
        }
        $(ZENTRAImpact.selectors.maxDepthView).html(text);
        $(ZENTRAImpact.selectors.maxDepth).val(ZENTRAImpact.maxDepth);

        // Set color widgets default values
        $(ZENTRAImpact.selectors.dependsColor).val(
            ZENTRAImpact.edgeColors[ZENTRAImpact.BACKWARD]
        );
        $(ZENTRAImpact.selectors.impactColor).val(
            ZENTRAImpact.edgeColors[ZENTRAImpact.FORWARD]
        );
        $(ZENTRAImpact.selectors.impactAndDependsColor).val(
            ZENTRAImpact.edgeColors[ZENTRAImpact.BOTH]
        );
    },

    /**
    * Set readonly and show toolbar
    */
    enableGraphEdition: function() {
        // Show toolbar
        $(this.selectors.save).show();
        $(this.selectors.addNode).show();
        $(this.selectors.addEdge).show();
        $(this.selectors.addCompound).show();
        $(this.selectors.deleteElement).show();
        $(this.selectors.impactSettings).show();
        $(this.selectors.sideToggle).show();

        // Keep track of readonly so that events handler can update their behavior
        this.readonly = false;
    },

    /**
    * Create ID for nodes and egdes
    *
    * @param {number} type (NODE or EDGE)
    * @param {string} a
    * @param {string} b
    *
    * @returns {string|null}
    */
    makeID: function(type, a, b) {
        switch (type) {
            case ZENTRAImpact.NODE:
                return a + "::" + b;
            case ZENTRAImpact.EDGE:
                return a + "->" + b;
        }

        return null;
    },

    /**
    * Helper to make an ID selector
    * We can't use the short syntax "#id" because our ids contains
    * non-alpha-numeric characters
    *
    * @param {string} id
    *
    * @returns {string}
    */
    makeIDSelector: function(id) {
        return "[id='" + id + "']";
    },

    /**
    * Reload the graph style
    */
    updateStyle: function() {
        this.cy.style(this.getNetworkStyle());
        // If either the source of the target node of an edge is hidden, hide the
        // edge too by setting it's dept to the maximum value
        this.cy.edges().forEach(function(edge) {
            var source = ZENTRAImpact.cy.filter(ZENTRAImpact.makeIDSelector(edge.data('source')));
            var target = ZENTRAImpact.cy.filter(ZENTRAImpact.makeIDSelector(edge.data('target')));
            if (source.visible() && target.visible()) {
                edge.data('depth', 0);
            } else {
                edge.data('depth', Number.MAX_VALUE);
            }
        });
    },

    /**
    * Compute flags and depth for each nodes
    */
    updateFlags: function() {
        /**
       * Assuming A is our starting node and B is a random node on the graph,
       * the depth of B is the shortest distance between AB and BA.
       */

        // Init flag to ZENTRAImpact.DEFAULT for all elements of the graph
        this.cy.elements().forEach(function(ele) {
            ele.data('flag', ZENTRAImpact.DEFAULT);
        });

        // First, calculate AB: Apply dijkstra on A and get distances for each
        // nodes
        var startNodeDijkstra = this.cy.elements().dijkstra(
            this.makeIDSelector(this.startNode),
            function() { return 1; }, // Same weight for each path
            true                      // Do not ignore edge directions
        );

        this.cy.$("node:childless").forEach(function(node) {
            var distanceAB = startNodeDijkstra.distanceTo(node);
            node.data('depth', distanceAB);

            // Set node as part of the "Forward" graph
            if (distanceAB !== Infinity) {
                node.data('flag', node.data('flag') | ZENTRAImpact.FORWARD);
            }
        });

        // Now, calculate BA: apply dijkstra on each nodes of the graph and
        // get the distance to A
        this.cy.$("node:childless").forEach(function(node) {
            // Skip A
            if (node.data('id') == ZENTRAImpact.startNode) {
                return;
            }

            var otherNodeDijkstra = ZENTRAImpact.cy.elements().dijkstra(
                node,
                function() { return 1; }, // Same weight for each path
                true                      // Do not ignore edge directions
            );

            var distanceBA = otherNodeDijkstra.distanceTo(
                ZENTRAImpact.makeIDSelector(ZENTRAImpact.startNode)
            );

            // If distance BA is shorter than distance AB, use it instead
            if (node.data('depth') > distanceBA) {
                node.data('depth', distanceBA);
            }

            // Set node as part of the "Backward" graph
            if (distanceBA !== Infinity) {
                node.data('flag', node.data('flag') | ZENTRAImpact.BACKWARD);
            }
        });

        // Set start node to this.BOTH so it doen't impact the computation of it's neighbors
        ZENTRAImpact.cy.$(ZENTRAImpact.makeIDSelector(ZENTRAImpact.startNode)).data(
            'flag',
            this.BOTH
        );

        // Handle compounds nodes, their depth should be the lowest depth amongst
        // their children
        this.cy.filter("node:parent").forEach(function(compound) {
            var lowestDepth = Infinity;
            var flag = ZENTRAImpact.DEFAULT;

            compound.children().forEach(function(childNode) {
                var childNodeDepth = childNode.data('depth');
                if (childNodeDepth < lowestDepth) {
                    lowestDepth = childNodeDepth;
                }

                flag = flag | childNode.data('flag');
            });

            compound.data('depth', lowestDepth);
            compound.data('flag', flag);
        });

        // Apply flag to edges so they can get the right colors
        this.cy.edges().forEach(function(edge) {
            var source = ZENTRAImpact.cy.$(ZENTRAImpact.makeIDSelector(edge.data('source')));
            var target = ZENTRAImpact.cy.$(ZENTRAImpact.makeIDSelector(edge.data('target')));

            edge.data('flag', source.data('flag') & target.data('flag'));
        });

        // Set start node to this.DEFAULT when all calculation are down so he is
        // always shown
        ZENTRAImpact.cy.$(ZENTRAImpact.makeIDSelector(ZENTRAImpact.startNode)).data(
            'flag',
            this.DEFAULT
        );

        ZENTRAImpact.updateStyle();
    },

    /**
    * Toggle impact/depends visibility
    *
    * @param {*} toToggle
    */
    toggleVisibility: function(toToggle) {
        // Update visibility setting
        ZENTRAImpact.directionVisibility[toToggle] = !ZENTRAImpact.directionVisibility[toToggle];
        ZENTRAImpact.updateFlags();
        ZENTRAImpact.cy.trigger("change");
    },

    /**
    * Set max depth of the graph
    * @param {Number} max max depth
    */
    setDepth: function(max) {
        ZENTRAImpact.maxDepth = max;

        if (max >= ZENTRAImpact.MAX_DEPTH) {
            max = "infinity";
            ZENTRAImpact.maxDepth = ZENTRAImpact.NO_DEPTH_LIMIT;
        }

        $(ZENTRAImpact.selectors.maxDepthView).text(max);
        ZENTRAImpact.updateStyle();
        ZENTRAImpact.cy.trigger("change");
    },

    /**
    * Ask the backend to build a graph from a specific node
    *
    * @param {Object} node
    * @returns {Array|null}
    */
    buildGraphFromNode: function(node) {
        node.action = "load";
        var dfd = jQuery.Deferred();

        // Request to backend
        $.ajax({
            type: "GET",
            url: CFG_ZENTRA.root_doc + "/ajax/impact.php",
            dataType: "json",
            data: node,
            success: function(data) {
                dfd.resolve(JSON.parse(data.graph), JSON.parse(data.params));
            },
            error: function () {
                dfd.reject();
            }
        });

        return dfd.promise();
    },

    /**
    * Get distance between two point A and B
    * @param {Object} a x, y
    * @param {Object} b x, y
    * @returns {Number}
    */
    getDistance: function(a, b) {
        return Math.sqrt(Math.pow(b.x - a.x, 2) + Math.pow(b.y - a.y, 2));
    },

    /**
    * Insert another new graph into the current one
    *
    * @param {Array}  graph
    * @param {Object} params
    * @param {Object} startNode data, x, y
    */
    insertGraph: function(graph, params, startNode) {
        var toAdd = [];
        var mainBoundingBox = this.cy.filter().boundingBox();

        // Try to add the new graph nodes
        var i;
        for (i=0; i<graph.length; i++) {
            var id = graph[i].data.id;
            // Check that the element is not already on the graph,
            if (this.cy.filter('[id="' + CSS.escape(id) + '"]').length > 0) {
                continue;
            }
            // Store node to add them at once with a layout
            toAdd.push(graph[i]);

            // Remove node from side list if needed
            if (graph[i].group == "nodes" && graph[i].data.color === undefined ) {
                var node_info = graph[i].data.id.split(ZENTRAImpact.NODE_ID_SEPERATOR);
                var itemtype = node_info[0];
                var items_id = node_info[1];
                $("p[data-id=" + CSS.escape(items_id) + "][data-type='" + CSS.escape(itemtype) + "']").remove();
            }
        }

        // Just place the node if only one result is found
        if (toAdd.length == 1) {
            toAdd[0].position = {
                x: startNode.x,
                y: startNode.y,
            };

            this.cy.add(toAdd);
            this.addToUndo(this.ACTION_ADD_NODE, {
                toAdd: toAdd[0]
            });
            return;
        }

        // Add nodes and apply layout
        var eles = this.cy.add(toAdd);

        var options;
        var savedPositions = null;
        if (params.positions === undefined) {
            options = this.getDagreLayout();
        } else {
            savedPositions = JSON.parse(params.positions);
            options = this.getPresetLayout(savedPositions);
        }

        // Place the layout anywhere to compute it's bounding box
        var layout = eles.layout(options);
        layout.run();
        this.generateMissingPositions();

        // Check if any of the new nodes have saved positions
        var hasNodesWithSavedPositions = false;
        if (savedPositions) {
            eles.nodes().forEach(function(node) {
                if (!node.isParent() && savedPositions[node.data('id')] !== undefined) {
                    hasNodesWithSavedPositions = true;
                }
            });
        }

        // Only apply positioning logic if we don't have saved positions for the new nodes
        if (!hasNodesWithSavedPositions) {
            // First, position the graph on the clicked area
            var newGraphBoundingBox = eles.boundingBox();
            var center = {
                x: (newGraphBoundingBox.x1 + newGraphBoundingBox.x2) / 2,
                y: (newGraphBoundingBox.y1 + newGraphBoundingBox.y2) / 2,
            };

            var centerToClickVector = [
                startNode.x - center.x,
                startNode.y - center.y,
            ];

            // Apply vector to each node
            eles.nodes().forEach(function(node) {
                if (!node.isParent()) {
                    node.position({
                        x: node.position().x + centerToClickVector[0],
                        y: node.position().y + centerToClickVector[1],
                    });
                }
            });

            newGraphBoundingBox = eles.boundingBox();
        } else {
            // If we have saved positions, just finish and don't apply collision detection
            this.cy.animate({
                center: {
                    eles : ZENTRAImpact.cy.filter(""),
                },
            });

            this.cy.getElementById(startNode.id).data("highlight", 1);

            // Set undo/redo data
            var undoData = {
                edges: eles.edges().map(function(edge){ return edge.data(); }),
                compounds: [],
                nodes: [],
            };
            eles.nodes().forEach(function(node) {
                if (node.isParent()) {
                    undoData.compounds.push({
                        compoundData    : _.clone(node.data()),
                        compoundChildren: node.children().map(function(n) {
                            return n.data('id');
                        }),
                    });
                } else {
                    undoData.nodes.push({
                        nodeData    : _.clone(node.data()),
                        nodePosition: _.clone(node.position()),
                    });
                }
            });
            this.addToUndo(this.ACTION_ADD_GRAPH, undoData);
            return;
        }

        // If the two bouding box overlap
        if (!(mainBoundingBox.x1 > newGraphBoundingBox.x2
         || newGraphBoundingBox.x1 > mainBoundingBox.x2
         || mainBoundingBox.y1 > newGraphBoundingBox.y2
         || newGraphBoundingBox.y1 > mainBoundingBox.y2)) {

            // We want to find the point "intersect", which is the closest
            // intersection between the point at the center of the new bounding box
            // and the main bouding bouding box.
            // We then want to find the point "closest" which is the vertice of
            // the new bounding box which is the closest to the center of the
            // main bouding box

            // Then the vector betwteen "intersect" and "closest" can be applied
            // to the new graph to make it "slide" out of the main graph

            // Center of the new graph
            center = {
                x: Math.round((newGraphBoundingBox.x1 + newGraphBoundingBox.x2) / 2),
                y: Math.round((newGraphBoundingBox.y1 + newGraphBoundingBox.y2) / 2),
            };

            var directions = [
                [1, 0], [0, 1], [-1, 0], [0, -1], [1, 1], [-1, 1], [-1, -1], [1, -1]
            ];

            var edges = [
                {
                    a: {x: Math.round(mainBoundingBox.x1), y: Math.round(mainBoundingBox.y1)},
                    b: {x: Math.round(mainBoundingBox.x2), y: Math.round(mainBoundingBox.y1)},
                },
                {
                    a: {x: Math.round(mainBoundingBox.x2), y: Math.round(mainBoundingBox.y1)},
                    b: {x: Math.round(mainBoundingBox.x1), y: Math.round(mainBoundingBox.y2)},
                },
                {
                    a: {x: Math.round(mainBoundingBox.x1), y: Math.round(mainBoundingBox.y2)},
                    b: {x: Math.round(mainBoundingBox.x2), y: Math.round(mainBoundingBox.y2)},
                },
                {
                    a: {x: Math.round(mainBoundingBox.x2), y: Math.round(mainBoundingBox.y2)},
                    b: {x: Math.round(mainBoundingBox.x1), y: Math.round(mainBoundingBox.y1)},
                }
            ];

            i = 0; // Safegard, no more than X tries
            var intersect;
            while (i < 50000) {
                directions.forEach(function(vector) {
                    if (intersect !== undefined) {
                        return;
                    }

                    var point = {
                        x: center.x + (vector[0] * i),
                        y: center.y + (vector[1] * i),
                    };

                    // Check if the point intersect with one of the edges
                    edges.forEach(function(edge) {
                        if (intersect !== undefined) {
                            return;
                        }

                        if ((ZENTRAImpact.getDistance(point, edge.a)
                     + ZENTRAImpact.getDistance(point, edge.b))
                     == ZENTRAImpact.getDistance(edge.a, edge.b)) {
                            // Found intersection
                            intersect = {
                                x: point.x,
                                y: point.y,
                            };
                        }
                    });
                });

                i++;

                if (intersect !== undefined) {
                    break;
                }
            }

            if (intersect !== undefined) {
            // Center of the main graph
                center = {
                    x: (mainBoundingBox.x1 + mainBoundingBox.x2) / 2,
                    y: (mainBoundingBox.y1 + mainBoundingBox.y2) / 2,
                };

                var vertices = [
                    {x: newGraphBoundingBox.x1, y: newGraphBoundingBox.y1},
                    {x: newGraphBoundingBox.x1, y: newGraphBoundingBox.y2},
                    {x: newGraphBoundingBox.x2, y: newGraphBoundingBox.y1},
                    {x: newGraphBoundingBox.x2, y: newGraphBoundingBox.y2},
                ];

                var closest;
                var min_dist;

                vertices.forEach(function(vertice) {
                    var dist = ZENTRAImpact.getDistance(vertice, center);
                    if (min_dist == undefined || dist < min_dist) {
                        min_dist = dist;
                        closest = vertice;
                    }
                });

                // Compute vector between closest and intersect
                var vector = [
                    intersect.x - closest.x,
                    intersect.y - closest.y,
                ];

                // Apply vector to each node
                eles.nodes().forEach(function(node) {
                    if (!node.isParent()) {
                        node.position({
                            x: node.position().x + vector[0],
                            y: node.position().y + vector[1],
                        });
                    }
                });
            }
        }

        this.generateMissingPositions();
        this.cy.animate({
            center: {
                eles : ZENTRAImpact.cy.filter(""),
            },
        });

        this.cy.getElementById(startNode.id).data("highlight", 1);

        // Set undo/redo data
        var data = {
            edges: eles.edges().map(function(edge){ return edge.data(); }),
            compounds: [],
            nodes: [],
        };
        eles.nodes().forEach(function(node) {
            if (node.isParent()) {
                data.compounds.push({
                    compoundData    : _.clone(node.data()),
                    compoundChildren: node.children().map(function(n) {
                        return n.data('id');
                    }),
                });
            } else {
                data.nodes.push({
                    nodeData    : _.clone(node.data()),
                    nodePosition: _.clone(node.position()),
                });
            }
        });
        this.addToUndo(this.ACTION_ADD_GRAPH, data);
    },

    /**
    * Set the colors
    *
    * @param {object} colors default, backward, forward, both
    */
    setEdgeColors: function (colors) {
        this.setColorIfExist(ZENTRAImpact.DEFAULT, colors.default);
        this.setColorIfExist(ZENTRAImpact.BACKWARD, colors.backward);
        this.setColorIfExist(ZENTRAImpact.FORWARD, colors.forward);
        this.setColorIfExist(ZENTRAImpact.BOTH, colors.both);
    },

    /**
    * Set color if exist
    *
    * @param {object} colors default, backward, forward, both
    */
    setColorIfExist: function (index, color) {
        if (color !== undefined) {
            this.edgeColors[index] = color;
        }
    },

    /**
    * Exit current edition mode and enter a new one
    *
    * @param {number} mode
    */
    setEditionMode: function (mode) {
        // Switching to a mode we are already in -> go to default
        if (this.editionMode == mode) {
            mode = ZENTRAImpact.EDITION_DEFAULT;
        }

        this.exitEditionMode();
        this.enterEditionMode(mode);
        this.editionMode = mode;
    },

    /**
    * Exit current edition mode
    */
    exitEditionMode: function() {
        switch (this.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                ZENTRAImpact.cy.nodes().ungrabify();
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                ZENTRAImpact.cy.nodes().ungrabify();
                $(ZENTRAImpact.selectors.sideToggleIcon).addClass('fa-chevron-left');
                $(ZENTRAImpact.selectors.sideToggleIcon).removeClass('fa-chevron-right');
                $(ZENTRAImpact.selectors.side).removeClass('impact-side-expanded');
                $(ZENTRAImpact.selectors.sidePanel).removeClass('impact-side-expanded');
                $(ZENTRAImpact.selectors.addNode).removeClass("active");
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                $(ZENTRAImpact.selectors.addEdge).removeClass("active");
                // Empty event data and remove tmp node
                ZENTRAImpact.eventData.addEdgeStart = null;
                ZENTRAImpact.cy.filter("#tmp_node").remove();
                break;

            case ZENTRAImpact.EDITION_DELETE:
                ZENTRAImpact.cy.filter().unselect();
                ZENTRAImpact.cy.data('todelete', 0);
                $(ZENTRAImpact.selectors.deleteElement).removeClass("active");
                break;

            case ZENTRAImpact.EDITION_ADD_COMPOUND:
                ZENTRAImpact.cy.panningEnabled(true);
                ZENTRAImpact.cy.boxSelectionEnabled(false);
                $(ZENTRAImpact.selectors.addCompound).removeClass("active");
                break;

            case ZENTRAImpact.EDITION_SETTINGS:
                ZENTRAImpact.cy.nodes().ungrabify();
                $(ZENTRAImpact.selectors.sideToggleIcon).addClass('fa-chevron-left');
                $(ZENTRAImpact.selectors.sideToggleIcon).removeClass('fa-chevron-right');
                $(ZENTRAImpact.selectors.side).removeClass('impact-side-expanded');
                $(ZENTRAImpact.selectors.sidePanel).removeClass('impact-side-expanded');
                $(ZENTRAImpact.selectors.impactSettings).removeClass("active");
                break;
        }
    },

    /**
    * Enter a new edition mode
    *
    * @param {number} mode
    */
    enterEditionMode: function(mode) {
        switch (mode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                ZENTRAImpact.clearHelpText();
                ZENTRAImpact.cy.nodes().grabify();
                $(ZENTRAImpact.impactContainer).css('cursor', "move");
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                ZENTRAImpact.cy.nodes().grabify();
                ZENTRAImpact.clearHelpText();
                $(ZENTRAImpact.selectors.sideToggleIcon).removeClass('fa-chevron-left');
                $(ZENTRAImpact.selectors.sideToggleIcon).addClass('fa-chevron-right');
                $(ZENTRAImpact.selectors.side).addClass('impact-side-expanded');
                $(ZENTRAImpact.selectors.sidePanel).addClass('impact-side-expanded');
                $(ZENTRAImpact.selectors.addNode).addClass("active");
                $(ZENTRAImpact.impactContainer).css('cursor', "move");
                $(ZENTRAImpact.selectors.sideSettings).hide();
                $(ZENTRAImpact.selectors.sideAddNode).show();
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                ZENTRAImpact.showHelpText(__("Draw a line between two assets to add an impact relation"));
                $(ZENTRAImpact.selectors.addEdge).addClass("active");
                $(ZENTRAImpact.impactContainer).css('cursor', "crosshair");
                break;

            case ZENTRAImpact.EDITION_DELETE:
                ZENTRAImpact.cy.filter().unselect();
                ZENTRAImpact.showHelpText(__("Click on an element to remove it from the network"));
                $(ZENTRAImpact.selectors.deleteElement).addClass("active");
                $(ZENTRAImpact.impactContainer).css('cursor', "move");
                break;

            case ZENTRAImpact.EDITION_ADD_COMPOUND:
                ZENTRAImpact.cy.panningEnabled(false);
                ZENTRAImpact.cy.boxSelectionEnabled(true);
                ZENTRAImpact.showHelpText(__("Draw a square containing the assets you wish to group"));
                $(ZENTRAImpact.selectors.addCompound).addClass("active");
                $(ZENTRAImpact.impactContainer).css('cursor', "crosshair");
                break;

            case ZENTRAImpact.EDITION_SETTINGS:
                ZENTRAImpact.cy.nodes().grabify();
                $(ZENTRAImpact.selectors.sideToggleIcon).removeClass('fa-chevron-left');
                $(ZENTRAImpact.selectors.sideToggleIcon).addClass('fa-chevron-right');
                $(ZENTRAImpact.selectors.side).addClass('impact-side-expanded');
                $(ZENTRAImpact.selectors.sidePanel).addClass('impact-side-expanded');
                $(ZENTRAImpact.selectors.impactSettings).addClass("active");
                $(ZENTRAImpact.selectors.sideAddNode).hide();
                $(ZENTRAImpact.selectors.sideSettings).show();
                break;
        }
    },

    /**
    * Hide the toolbar and show an help text
    *
    * @param {string} text
    */
    showHelpText: function(text) {
        $(ZENTRAImpact.selectors.helpText).html(text).show();
    },

    /**
    * Hide the help text and show the toolbar
    */
    clearHelpText: function() {
        $(ZENTRAImpact.selectors.helpText).hide();
    },

    /**
    * Export the graph in the given format
    *
    * @param {string} format
    * @param {boolean} transparentBackground (png only)
    *
    * @returns {Object} filename, filecontent
    */
    download: function(format, transparentBackground) {
        var filename;
        var filecontent;

        // Create fake link
        ZENTRAImpact.impactContainer.append("<a id='impact_download'></a>");
        var link = $('#impact_download');

        switch (format) {
            case 'png':
                filename = "impact.png";
                filecontent = this.cy.png({
                    bg: transparentBackground ? "transparent" : "white"
                });
                break;

            case 'jpeg':
                filename = "impact.jpeg";
                filecontent = this.cy.jpg();
                break;
        }

        // Trigger download and remore the link
        link.prop('download', filename);
        link.prop("href", filecontent);
        link[0].click();
        link.remove();
    },

    /**
    * Get node at target position
    *
    * @param {Object} position x, y
    * @param {function} filter if false return null
    */
    getNodeAt: function(position, filter) {
        var nodes = this.cy.nodes();

        for (var i=0; i<nodes.length; i++) {
            if (nodes[i].boundingBox().x1 < position.x
          && nodes[i].boundingBox().x2 > position.x
          && nodes[i].boundingBox().y1 < position.y
          && nodes[i].boundingBox().y2 > position.y) {
            // Check if the node is excluded
                if (filter(nodes[i])) {
                    return nodes[i];
                }
            }
        }

        return null;
    },

    /**
    * Enable the save button
    */
    showCleanWorkspaceStatus: function() {
        $(ZENTRAImpact.selectors.save).removeClass('dirty');
        $(ZENTRAImpact.selectors.save).removeClass('clean'); // Needed for animations if the workspace is not dirty
        $(ZENTRAImpact.selectors.save).addClass('clean');
    },

    /**
    * Enable the save button
    */
    showDirtyWorkspaceStatus: function() {
        $(ZENTRAImpact.selectors.save).removeClass('clean');
        $(ZENTRAImpact.selectors.save).addClass('dirty');
    },

    /**
    * Enable the save button
    */
    showDefaultWorkspaceStatus: function() {
        $(ZENTRAImpact.selectors.save).removeClass('clean');
        $(ZENTRAImpact.selectors.save).removeClass('dirty');
    },

    /**
    * Build the ongoing dialog content according to the list of ITILObjects
    *
    * @param {Object} ITILObjects requests, incidents, changes, problems
    *
    * @returns {string}
    */
    buildOngoingDialogContent: function(ITILObjects) {
        return this.listElements(_.unescape(__("Requests")), ITILObjects.requests, "ticket")
         + this.listElements(_.unescape(__("Incidents")), ITILObjects.incidents, "ticket")
         + this.listElements(_.unescape(__("Changes")), ITILObjects.changes , "change")
         + this.listElements(_.unescape(__("Problems")), ITILObjects.problems, "problem");
    },

    /**
    * Build an html list
    *
    * @param {string} title requests, incidents, changes, problems
    * @param {string} elements requests, incidents, changes, problems
    * @param {string} url key used to generate the URL
    *
    * @returns {string}
    */
    listElements: function(title, elements, url) {
        var html = "";

        if (elements.length > 0) {
            html += "<h3>" + _.escape(title) + "</h3>";
            html += "<ul>";

            elements.forEach(function(element) {
                var link = CFG_ZENTRA.root_doc + "/front/" + url + ".form.php?id=" + element.id;
                html += '<li><a target="_blank" href="' + _.escape(link) + '">' + _.escape(element.name)
               + '</a></li>';
            });
            html += "</ul>";
        }
        return html;
    },

    /**
    * Add a compound from the selected nodes
    */
    addCompoundFromSelection: _.debounce(function(){
        // Check that there is enough selected nodes
        if (ZENTRAImpact.eventData.boxSelected.length < 1) {
            alert(__("You need to select at least 1 asset to make a group"));
        } else {
            // Create the compound
            var newCompound = ZENTRAImpact.cy.add({
                group: 'nodes',
                data: {color: '#dadada'},
            });

            // Log event data (for undo)
            ZENTRAImpact.eventData.newCompound = {
                data: {id: newCompound.data('id')},
                children: [],
            };

            // Set parent for coumpound member
            ZENTRAImpact.eventData.boxSelected.forEach(function(ele) {
                ele.move({'parent': newCompound.data('id')});
                ZENTRAImpact.eventData.newCompound.children.push(ele.data('id'));
            });

            // Show edit dialog
            ZENTRAImpact.showEditCompoundDialog(newCompound);

            // Back to default mode
            ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_DEFAULT);
        }

        // Clear the selection
        ZENTRAImpact.eventData.boxSelected = [];
        ZENTRAImpact.cy.filter(":selected").unselect();
    }, 100, false),

    /**
    * Remove an element from the graph
    *
    * @param {object} ele
    */
    deleteFromGraph: function(ele) {
        if (ele.data('id') == ZENTRAImpact.startNode) {
            alert("Can't remove starting node");
            return;
        }

        // Log for undo/redo
        var deleted = {
            edges: [],
            nodes: [],
            compounds: []
        };

        if (ele.isEdge()) {
            // Case 1: removing an edge
            deleted.edges.push(_.clone(ele.data()));
            ele.remove();
        } else if (ele.isParent()) {
            // Case 2: removing a compound

            // Set undo/redo data
            deleted.compounds.push({
                compoundData    : _.clone(ele.data()),
                compoundChildren: ele.children().map(function(node) {
                    return node.data('id');
                }),
            });

            // Remove only the parent
            ele.children().move({parent: null});
            ele.remove();
        } else {
            // Case 3: removing a node
            // Remove parent if last child of a compound
            if (!ele.isOrphan() && ele.parent().children().length <= 2) {
                var parent = ele.parent();

                // Set undo/redo data
                deleted.compounds.push({
                    compoundData    : _.clone(parent.data()),
                    compoundChildren: parent.children().map(function(node) {
                        return node.data('id');
                    }),
                });

                parent.children().move({parent: null});
                parent.remove();
            }

            // Set undo/redo data
            deleted.nodes.push({
                nodeData: _.clone(ele.data()),
                nodePosition: _.clone(ele.position()),
            });
            deleted.edges = deleted.edges.concat(ele.connectedEdges(function(edge) {
            // Check for duplicates
                var exist = false;
                deleted.edges.forEach(function(deletedEdge) {
                    if (deletedEdge.id == edge.data('id')) {
                        exist = true;
                    }
                });

                // In case of multiple deletion, check in the buffer too
                if (ZENTRAImpact.eventData.multipleDeletion != null) {
                    ZENTRAImpact.eventData.multipleDeletion.edges.forEach(
                        function(deletedEdge) {
                            if (deletedEdge.id == edge.data('id')) {
                                exist = true;
                            }
                        }
                    );
                }

                return !exist;
            }).map(function(ele){
                return ele.data();
            }));

            // Remove all edges connected to this node from graph and delta
            ele.remove();
        }

        // Update flags
        ZENTRAImpact.updateFlags();

        // Multiple deletion, set the data in eventData buffer so it can be added
        // as a simple undo/redo entry later
        if (this.eventData.multipleDeletion != null) {
            this.eventData.multipleDeletion.edges = this.eventData.multipleDeletion.edges.concat(deleted.edges);
            this.eventData.multipleDeletion.nodes = this.eventData.multipleDeletion.nodes.concat(deleted.nodes);
            this.eventData.multipleDeletion.compounds = this.eventData.multipleDeletion.compounds.concat(deleted.compounds);
        } else {
            this.addToUndo(this.ACTION_DELETE, deleted);
        }
    },

    /**
    * Toggle fullscreen mode
    */
    toggleFullscreen: function() {
        this.fullscreen = !this.fullscreen;
        $(this.selectors.toggleFullscreen).toggleClass('active');
        $(this.impactContainer).toggleClass('fullscreen');
        $(this.selectors.side).toggleClass('fullscreen');

        if (this.fullscreen) {
            $(this.impactContainer).children("canvas:eq(0)").css({
                height: "100vh"
            });
            $('html, body').css('overflow', 'hidden');
        } else {
            $(this.impactContainer).children("canvas:eq(0)").css({
                height: "unset"
            });
            $('html, body').css('overflow', 'unset');
        }

        ZENTRAImpact.cy.resize();
    },

    /**
    * Check if a given position match the hitbox of a badge
    *
    * @param {Object}   renderedPosition  {x, y}
    * @param {Boolean}  trigger           should we trigger the link if there
    *                                     is a match ?
    * @param {Boolean}  blank
    * @returns {Boolean}
    */
    checkBadgeHitboxes: function (renderedPosition, trigger, blank) {
        var hit = false;
        var margin = 5 * ZENTRAImpact.cy.zoom();

        ZENTRAImpact.badgesHitboxes.forEach(function(badgeHitboxDetails) {
            if (hit) {
                return;
            }

            var position = badgeHitboxDetails.position;
            var bb = {
                x1: position.x - margin,
                x2: position.x + margin,
                y1: position.y - margin,
                y2: position.y + margin,
            };

            if (bb.x1 < renderedPosition.x && bb.x2 > renderedPosition.x
            && bb.y1 < renderedPosition.y && bb.y2 > renderedPosition.y) {
                hit = true;

                if (trigger) {
                    let target = badgeHitboxDetails.target;

                    let next_criteria = 0;
                    if (badgeHitboxDetails.id_option) {
                        // Add items_id/itemtype metacriteria since we know the ID field for the asset type
                        target += `&criteria[0][link]=AND&criteria[0][field]=${badgeHitboxDetails.id_option}&criteria[0][itemtype]=${badgeHitboxDetails.itemtype}&criteria[0][meta]=1&criteria[0][searchtype]=contains&criteria[0][value]=${badgeHitboxDetails.id}`;
                        next_criteria = 1;
                    } else {
                        // Asset type doesn't have an ID metacriteria that we know of so fallback to the options directly on the ITIL item
                        // Add items_id criteria
                        target += `&criteria[0][link]=AND&criteria[0][field]=13&criteria[0][searchtype]=contains&criteria[0][value]=${badgeHitboxDetails.id}`;
                        // Add itemtype criteria
                        target += `&criteria[1][link]=AND&criteria[1][field]=131&criteria[1][searchtype]=equals&criteria[1][value]=${badgeHitboxDetails.itemtype}`;
                        next_criteria = 2;
                    }

                    // Add type criteria (incident)
                    target += `&criteria[${next_criteria}][link]=AND&criteria[${next_criteria}][field]=14&criteria[${next_criteria}][searchtype]=equals&criteria[${next_criteria}][value]=1`;
                    // Add status criteria (not solved)
                    target += `&criteria[${next_criteria + 1}][link]=AND&criteria[${next_criteria + 1}][field]=12&criteria[${next_criteria + 1}][searchtype]=equals&criteria[${next_criteria + 1}][value]=notold`;

                    if (blank) {
                        window.open(target);
                    } else {
                        window.location.href = target;
                    }
                }
            }
        });

        return hit;
    },

    /**
    * Handle global click events
    *
    * @param {JQuery.Event} event
    */
    onClick: function (event) {
        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                break;

            case ZENTRAImpact.EDITION_DELETE:
                break;
        }

        ZENTRAImpact.checkBadgeHitboxes(event.renderedPosition, true, ZENTRAImpact.eventData.ctrlDown);
    },

    /**
    * Handle click on edge
    *
    * @param {JQuery.Event} event
    */
    edgeOnClick: function (event) {
        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                break;

            case ZENTRAImpact.EDITION_DELETE:
            // Remove the edge from the graph
                ZENTRAImpact.deleteFromGraph(event.target);
                break;
        }
    },

    /**
    * Handle click on node
    *
    * @param {JQuery.Event} event
    */
    nodeOnClick: function (event) {
        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                if (ZENTRAImpact.eventData.lastClicktimestamp != null) {
                    // Trigger homemade double click event
                    if (event.timeStamp - ZENTRAImpact.eventData.lastClicktimestamp < 500
                  && event.target == ZENTRAImpact.eventData.lastClickTarget) {
                        event.target.trigger('doubleClick', event);
                    }
                }

                ZENTRAImpact.eventData.lastClicktimestamp = event.timeStamp;
                ZENTRAImpact.eventData.lastClickTarget = event.target;
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                break;

            case ZENTRAImpact.EDITION_DELETE:
                ZENTRAImpact.deleteFromGraph(event.target);
                break;
        }
    },

    /**
    * Handle end of box selection event
    *
    * @param {JQuery.Event} event
    */
    onBox: function (event) {
        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                break;

            case ZENTRAImpact.EDITION_DELETE:
                break;

            case ZENTRAImpact.EDITION_ADD_COMPOUND:
                var ele = event.target;
                // Add node to selected list if he is not part of a compound already
                if (ele.isNode() && ele.isOrphan() && !ele.isParent()) {
                    ZENTRAImpact.eventData.boxSelected.push(ele);
                }
                ZENTRAImpact.addCompoundFromSelection();
                break;
        }
    },

    /**
    * Handle any graph modification
    *
    * @param {*} event
    */
    onChange: function() {
        ZENTRAImpact.showDirtyWorkspaceStatus();

        // Remove hightligh for recently inserted graph
        ZENTRAImpact.cy.$("[highlight][!start_node]").data("highlight", 0);
    },

    /**
    * Double click handler
    * @param {JQuery.Event} event
    */
    onDoubleClick: function(event) {
        if (event.target.isParent()) {
            // Open edit dialog on compound nodes
            ZENTRAImpact.showEditCompoundDialog(event.target);
        } else if (event.target.isNode()) {
            // Go to on nodes
            window.open(event.target.data('link'));
        }
    },

    /**
    * Handle "grab" event
    *
    * @param {Jquery.event} event
    */
    onGrabOn: function(event) {
        // Store original position (shallow copy)
        ZENTRAImpact.eventData.grabNodePosition = {
            x: event.target.position().x,
            y: event.target.position().y,
        };

        // Store original parent (shallow copy)
        var parent = null;
        if (event.target.parent() !== undefined) {
            parent = event.target.parent().data('id');
        }
        ZENTRAImpact.eventData.grabNodeParent = parent;
    },

    /**
    * Handle "free" event
    * @param {Jquery.Event} event
    */
    onFreeOn: function(event) {
        var parent = null;
        if (event.target.parent() !== undefined) {
            parent = event.target.parent().data('id');
        }

        var newParent = null;
        if (parent !== ZENTRAImpact.eventData.grabNodeParent) {
            newParent = parent;
        }

        // If there was a real position change
        if (ZENTRAImpact.eventData.grabNodePosition.x !== event.target.position().x
         || ZENTRAImpact.eventData.grabNodePosition.y !== event.target.position().y) {

            ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_MOVE, {
                node: event.target.data('id'),
                oldPosition: ZENTRAImpact.eventData.grabNodePosition,
                newPosition: {
                    x: event.target.position().x,
                    y: event.target.position().y,
                },
                newParent: newParent,
            });
        }
    },

    /**
    * Remove handler
    * @param {JQuery.Event} event
    */
    onRemove: function(event) {
        if (event.target.isNode() && !event.target.isParent()) {
            var itemtype = event.target.data('id')
                .split(ZENTRAImpact.NODE_ID_SEPERATOR)[0];

            // If a node was deleted and its itemtype is the same as the one
            // selected in the add node panel, refresh the search
            if (itemtype == ZENTRAImpact.selectedItemtype) {
                $(ZENTRAImpact.selectors.sideSearchResults).html("");
                ZENTRAImpact.searchAssets(
                    ZENTRAImpact.selectedItemtype,
                    JSON.stringify(ZENTRAImpact.getUsedAssets()),
                    $(ZENTRAImpact.selectors.sideFilterAssets).val(),
                    0
                );
            }
        }
    },

    /**
    * Handler for key down events
    *
    * @param {JQuery.Event} event
    */
    onKeyDown: function(event) {
        // Ignore key events if typing inside input
        if (event.target.nodeName == "INPUT") {
            return;
        }

        switch (event.which) {
            // Shift
            case 16:
                if (event.ctrlKey) {
                    // Enter add compound edge mode
                    if (ZENTRAImpact.editionMode != ZENTRAImpact.EDITION_ADD_COMPOUND) {
                        if (ZENTRAImpact.eventData.previousEditionMode === undefined) {
                            ZENTRAImpact.eventData.previousEditionMode = ZENTRAImpact.editionMode;
                        }
                        ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_ADD_COMPOUND);
                    }
                } else {
                    // Enter edit edge mode
                    if (ZENTRAImpact.editionMode != ZENTRAImpact.EDITION_ADD_EDGE) {
                        if (ZENTRAImpact.eventData.previousEditionMode === undefined) {
                            ZENTRAImpact.eventData.previousEditionMode = ZENTRAImpact.editionMode;
                        }
                        ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_ADD_EDGE);
                    }
                }
                break;

                // Ctrl
            case 17:
                ZENTRAImpact.eventData.ctrlDown = true;
                break;

                // ESC
            case 27:
            // Exit specific edition mode
                if (ZENTRAImpact.editionMode != ZENTRAImpact.EDITION_DEFAULT) {
                    ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_DEFAULT);
                }
                break;

                // Delete
            case 46:
                if (ZENTRAImpact.readonly) {
                    break;
                }

                // Prepare multiple deletion buffer (for undo/redo)
                ZENTRAImpact.eventData.multipleDeletion = {
                    edges    : [],
                    nodes    : [],
                    compounds: [],
                };

                // Delete selected element(s)
                ZENTRAImpact.cy.filter(":selected").forEach(function(ele) {
                    ZENTRAImpact.deleteFromGraph(ele);
                });

                // Set undo/redo data
                ZENTRAImpact.addToUndo(
                    ZENTRAImpact.ACTION_DELETE,
                    ZENTRAImpact.eventData.multipleDeletion
                );

                // Reset multiple deletion buffer (for undo/redo)
                ZENTRAImpact.eventData.multipleDeletion = null;
                break;

                // CTRL + Y
            case 89:
                if (!event.ctrlKey) {
                    break;
                }

                ZENTRAImpact.redo();
                break;

                // CTRL + Z / CTRL + SHIFT + Z
            case 90:
                if (!event.ctrlKey) {
                    break;
                }

                if (event.shiftKey) {
                    ZENTRAImpact.redo();
                } else {
                    ZENTRAImpact.undo();
                }

                break;
        }
    },

    /**
    * Handler for key down events
    *
    * @param {JQuery.Event} event
    */
    onKeyUp: function(event) {
        switch (event.which) {
            // Shift
            case 16:
            // Return to previous edition mode if needed
                if (ZENTRAImpact.eventData.previousEditionMode !== undefined
               && (ZENTRAImpact.editionMode == ZENTRAImpact.EDITION_ADD_EDGE
                  || ZENTRAImpact.editionMode == ZENTRAImpact.EDITION_ADD_COMPOUND)
                ) {
                    ZENTRAImpact.setEditionMode(ZENTRAImpact.eventData.previousEditionMode);
                    ZENTRAImpact.eventData.previousEditionMode = undefined;
                }
                break;

                // Ctrl
            case 17:
            // Return to previous edition mode if needed
                if (ZENTRAImpact.editionMode == ZENTRAImpact.EDITION_ADD_COMPOUND
               && ZENTRAImpact.eventData.previousEditionMode !== undefined) {
                    ZENTRAImpact.setEditionMode(ZENTRAImpact.eventData.previousEditionMode);
                    ZENTRAImpact.eventData.previousEditionMode = undefined;
                }
                ZENTRAImpact.eventData.ctrlDown = false;
                break;
        }
    },

    /**
    * Handle mousedown events on nodes
    *
    * @param {JQuery.Event} event
    */
    nodeOnMousedown: function (event) {
        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                $(ZENTRAImpact.impactContainer).css('cursor', "grabbing");

                // If we are not on a compound node or a node already inside one
                if (event.target.isOrphan() && !event.target.isParent()) {
                    ZENTRAImpact.eventData.grabNodeStart = event.target;
                }
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                if (!event.target.isParent()) {
                    ZENTRAImpact.eventData.addEdgeStart = this.data('id');
                }
                break;

            case ZENTRAImpact.EDITION_DELETE:
                break;

            case ZENTRAImpact.EDITION_ADD_COMPOUND:
                break;
        }
    },

    /**
    * Handle mouseup events
    *
    * @param {JQuery.Event} event
    */
    onMouseUp: function(event) {
        if (event.target.data('id') != undefined && event.target.isNode()) {
            // Handler for nodes
            ZENTRAImpact.nodeOnMouseup();
        }
        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
            // Exit if no start node
                if (ZENTRAImpact.eventData.addEdgeStart == null) {
                    return;
                }

                // Reset addEdgeStart
                var startEdge = ZENTRAImpact.eventData.addEdgeStart; // Keep a copy to use later
                ZENTRAImpact.eventData.addEdgeStart = null;

                // Remove current tmp collection
                event.cy.remove(ZENTRAImpact.eventData.tmpEles);
                var edgeID = ZENTRAImpact.eventData.tmpEles.data('id');
                ZENTRAImpact.eventData.tmpEles = null;

                // Option 1: Edge between a node and the fake tmp_node -> ignore
                if (edgeID == 'tmp_node') {
                    return;
                }

                var edgeDetails = edgeID.split(ZENTRAImpact.EDGE_ID_SEPERATOR);

                // Option 2: Edge between two nodes that already exist -> ignore
                if (event.cy.filter('edge[id="' + edgeID + '"]').length > 0) {
                    return;
                }

                // Option 3: Both end of the edge are actually the same node -> ignore
                if (startEdge == edgeDetails[1]) {
                    return;
                }

                // Option 4: Edge between two nodes that does not exist yet -> create it!
                var data = {
                    id: edgeID,
                    source: startEdge,
                    target: edgeDetails[1]
                };
                event.cy.add({
                    group: 'edges',
                    data: data,
                });
                ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_ADD_EDGE, _.clone(data));

                // Update dependencies flags according to the new link
                ZENTRAImpact.updateFlags();

                break;

            case ZENTRAImpact.EDITION_DELETE:
                break;
        }
    },

    /**
    * Handle mouseup events on nodes
    *
    * @param {JQuery.Event} event
    */
    nodeOnMouseup: function () {
        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                $(ZENTRAImpact.impactContainer).css('cursor', "grab");

                // Reset eventData for node grabbing
                ZENTRAImpact.eventData.grabNodeStart = null;
                ZENTRAImpact.eventData.boundingBox = null;

                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                break;

            case ZENTRAImpact.EDITION_DELETE:
                break;
        }
    },

    /**
    * Handle mousemove events on nodes
    *
    * @param {JQuery.Event} event
    */
    onMousemove: _.throttle(function(event) {
        var node;

        // Check for badges hitboxes
        if (ZENTRAImpact.checkBadgeHitboxes(event.renderedPosition, false, false)
         && !ZENTRAImpact.eventData.showPointerForBadge) {
            // Entering a badge hitbox
            ZENTRAImpact.eventData.showPointerForBadge = true;

            // Store previous cursor and show pointer
            ZENTRAImpact.eventData.previousCursor = $(ZENTRAImpact.impactContainer).css('cursor');
            $(ZENTRAImpact.impactContainer).css('cursor', "pointer");
        } else if (ZENTRAImpact.eventData.showPointerForBadge
         && !ZENTRAImpact.checkBadgeHitboxes(event.renderedPosition, false, false)) {
            // Exiiting a badge hitbox
            ZENTRAImpact.eventData.showPointerForBadge = false;

            // Reset to previous cursor
            $(ZENTRAImpact.impactContainer).css(
                'cursor',
                ZENTRAImpact.eventData.previousCursor
            );
        }

        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
            case ZENTRAImpact.EDITION_ADD_NODE:

                // No action if we are not grabbing a node
                if (ZENTRAImpact.eventData.grabNodeStart == null) {
                    return;
                }

                // Look for a compound at the cursor position
                node = ZENTRAImpact.getNodeAt(event.position, function(node) {
                    return node.isParent();
                });

                if (node) {
                    // If we have a bounding box defined, the grabbed node is already
                    // being placed into a compound, we need to check if it was moved
                    // outside this original bouding box to know if the user is
                    // trying to move it away from the compound
                    if (ZENTRAImpact.eventData.boundingBox != null) {
                        // If the user tried to move out of the compound
                        if (ZENTRAImpact.eventData.boundingBox.x1 > event.position.x
                     || ZENTRAImpact.eventData.boundingBox.x2 < event.position.x
                     || ZENTRAImpact.eventData.boundingBox.y1 > event.position.y
                     || ZENTRAImpact.eventData.boundingBox.y2 < event.position.y) {
                            // Remove it from the compound
                            ZENTRAImpact.eventData.grabNodeStart.move({parent: null});
                            ZENTRAImpact.eventData.boundingBox = null;
                        }
                    } else {
                        // If we found a compound, add the grabbed node inside
                        ZENTRAImpact.eventData.grabNodeStart.move({parent: node.data('id')});

                        // Store the original bouding box of the compound
                        ZENTRAImpact.eventData.boundingBox = node.boundingBox();
                    }
                } else {
                    // Else; reset it's parent so it can be removed from any temporary
                    // compound while the user is stil grabbing
                    ZENTRAImpact.eventData.grabNodeStart.move({parent: null});
                }

                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
            // No action if we are not placing an edge
                if (ZENTRAImpact.eventData.addEdgeStart == null) {
                    return;
                }

                // Remove current tmp collection
                if (ZENTRAImpact.eventData.tmpEles != null) {
                    event.cy.remove(ZENTRAImpact.eventData.tmpEles);
                }

                node = ZENTRAImpact.getNodeAt(event.position, function(node) {
                    var nodeID = node.data('id');

                    // Can't link to itself
                    if (nodeID == ZENTRAImpact.eventData.addEdgeStart) {
                        return false;
                    }

                    // Can't link to parent
                    if (node.isParent()) {
                        return false;
                    }

                    // The created edge shouldn't already exist
                    var edgeID = ZENTRAImpact.makeID(ZENTRAImpact.EDGE, ZENTRAImpact.eventData.addEdgeStart, nodeID);
                    if (ZENTRAImpact.cy.filter('edge[id="' + edgeID + '"]').length > 0) {
                        return false;
                    }

                    // The node must be visible
                    if (!ZENTRAImpact.cy.getElementById(nodeID).visible()) {
                        return false;
                    }

                    return true;
                });

                if (node != null) {
                    node = node.data('id');

                    // Add temporary edge to node hovered by the user
                    ZENTRAImpact.eventData.tmpEles = event.cy.add([
                        {
                            group: 'edges',
                            data: {
                                id: ZENTRAImpact.makeID(ZENTRAImpact.EDGE, ZENTRAImpact.eventData.addEdgeStart, node),
                                source: ZENTRAImpact.eventData.addEdgeStart,
                                target: node,
                            }
                        }
                    ]);
                } else {
                    // Add temporary edge to a new invisible node at mouse position
                    ZENTRAImpact.eventData.tmpEles = event.cy.add([
                        {
                            group: 'nodes',
                            data: {
                                id: 'tmp_node',
                            },
                            position: {
                                x: event.position.x,
                                y: event.position.y
                            }
                        },
                        {
                            group: 'edges',
                            data: {
                                id: ZENTRAImpact.makeID(
                                    ZENTRAImpact.EDGE,
                                    ZENTRAImpact.eventData.addEdgeStart,
                                    "tmp_node"
                                ),
                                source: ZENTRAImpact.eventData.addEdgeStart,
                                target: 'tmp_node',
                            }
                        }
                    ]);
                }
                break;

            case ZENTRAImpact.EDITION_DELETE:
                break;
        }
    }, 25),

    /**
    * Handle global mouseover events
    *
    * @param {JQuery.Event} event
    */
    onMouseover: function(event) {
        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
            // No valid target, no action needed
                if (event.target.data('id') == undefined) {
                    break;
                }

                if (event.target.isNode()) {
                    if (!ZENTRAImpact.eventData.showPointerForBadge) {
                        // Don't alter the cursor if hovering a badge
                        $(ZENTRAImpact.impactContainer).css('cursor', "grab");
                    }
                } else if (event.target.isEdge()) {
                    // If mouseover on edge, show default cursor and disable panning
                    ZENTRAImpact.cy.panningEnabled(false);
                    if (!ZENTRAImpact.eventData.showPointerForBadge) {
                        // Don't alter the cursor if hovering a badge
                        $(ZENTRAImpact.impactContainer).css('cursor', "default");
                    }
                }
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                if (event.target.data('id') == undefined) {
                    break;
                }

                if (event.target.isNode()) {
                    // If mouseover on node, show grab cursor
                    $(ZENTRAImpact.impactContainer).css('cursor', "grab");
                } else if (event.target.isEdge()) {
                    // If mouseover on edge, show default cursor and disable panning
                    $(ZENTRAImpact.impactContainer).css('cursor', "default");
                    ZENTRAImpact.cy.panningEnabled(false);
                }
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                break;

            case ZENTRAImpact.EDITION_DELETE:
                if (event.target.data('id') == undefined) {
                    break;
                }

                $(ZENTRAImpact.impactContainer).css('cursor', "default");
                var id = event.target.data('id');

                // Remove red overlay
                event.cy.filter().data('todelete', 0);
                event.cy.filter().unselect();

                // Store here if one default node
                if (event.target.data('id') == ZENTRAImpact.startNode) {
                    $(ZENTRAImpact.impactContainer).css('cursor', "not-allowed");
                    break;
                }

                // Add red overlay
                event.target.data('todelete', 1);
                event.target.select();

                if (event.target.isNode()){
                    var sourceFilter = "edge[source='" + CSS.escape(id) + "']";
                    var targetFilter = "edge[target='" + CSS.escape(id) + "']";
                    event.cy.filter(sourceFilter + ", " + targetFilter)
                        .data('todelete', 1)
                        .select();
                }
                break;
        }
    },

    /**
    * Handle global mouseout events
    *
    * @param {JQuery.Event} event
    */
    onMouseout: function(event) {
        switch (ZENTRAImpact.editionMode) {
            case ZENTRAImpact.EDITION_DEFAULT:
                if (!ZENTRAImpact.eventData.showPointerForBadge) {
                    // Don't alter the cursor if hovering a badge
                    $(ZENTRAImpact.impactContainer).css('cursor', "move");
                }

                // Re-enable panning in case the mouse was over an edge
                ZENTRAImpact.cy.panningEnabled(true);
                break;

            case ZENTRAImpact.EDITION_ADD_NODE:
                if (!ZENTRAImpact.eventData.showPointerForBadge) {
                    // Don't alter the cursor if hovering a badge
                    $(ZENTRAImpact.impactContainer).css('cursor', "move");
                }
                // Re-enable panning in case the mouse was over an edge
                ZENTRAImpact.cy.panningEnabled(true);
                break;

            case ZENTRAImpact.EDITION_ADD_EDGE:
                break;

            case ZENTRAImpact.EDITION_DELETE:
            // Remove red overlay
                event.cy.filter().data('todelete', 0);
                event.cy.filter().unselect();
                if (!ZENTRAImpact.eventData.showPointerForBadge) {
                    // Don't alter the cursor if hovering a badge
                    $(ZENTRAImpact.impactContainer).css('cursor', "move");
                }
                break;
        }
    },

    /**
    * Handle "goTo" menu event
    *
    * @param {JQuery.Event} event
    */
    menuOnGoTo: function(event) {
        window.open(event.target.data('link'));
    },

    /**
    * Handle "showOngoing" menu event
    *
    * @param {JQuery.Event} event
    */
    menuOnShowOngoing: function(event) {
        ZENTRAImpact.showOngoingDialog(event.target.data('ITILObjects'));
    },

    /**
    * Handle "EditCompound" menu event
    *
    * @param {JQuery.Event} event
    */
    menuOnEditCompound: function (event) {
        ZENTRAImpact.showEditCompoundDialog(event.target);
    },

    /**
    * Handler for "removeFromCompound" action
    *
    * @param {JQuery.Event} event
    */
    menuOnRemoveFromCompound: function(event) {
        var parent = ZENTRAImpact.cy.getElementById(
            event.target.data('parent')
        );

        // Undo log
        ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_REMOVE_FROM_COMPOUND, {
            nodeData    : _.clone(event.target.data()),
            compoundData: _.clone(parent.data()),
            children    : parent.children().map(function(node) {
                return node.data('id');
            }),
        });

        // Remove node from compound
        event.target.move({parent: null});

        // Destroy compound if only one or zero member left
        if (parent.children().length < 2) {
            parent.children().move({parent: null});
            ZENTRAImpact.cy.remove(parent);
        }
    },

    /**
     * Handle "EditEdge" menu event
     *
     * @param {JQuery.Event} event
     */
    menuOnEditEdge: function (event) {
        ZENTRAImpact.showEditEdgeDialog(event.target);
    },

    /**
    * Handler for "delete" menu action
    *
    * @param {JQuery.Event} event
    */
    menuOnDelete: function(event){
        ZENTRAImpact.deleteFromGraph(event.target);
    },

    /**
    * Ask the backend for available assets to insert into the graph
    *
    * @param {String} itemtype
    * @param {Array}  used
    * @param {String} filter
    * @param {Number} page
    */
    searchAssets: function(itemtype, used, filter, page) {
        var hidden = ZENTRAImpact.cy
            .nodes(ZENTRAImpact.getHiddenSelector())
            .filter(function(node) {
                return !node.isParent();
            })
            .map(function(node) {
                return node.data('id');
            });

        $(ZENTRAImpact.selectors.sideSearchSpinner).show();
        $(ZENTRAImpact.selectors.sideSearchNoResults).hide();
        $.ajax({
            type: "GET",
            url: $(ZENTRAImpact.selectors.form).prop('action'),
            data: {
                'action'  : 'search',
                'itemtype': itemtype,
                'used'    : used,
                'filter'  : filter,
                'page'    : page,
            },
            success: function(data){
                $.each(data.items, function(index, value) {
                    var graph_id = itemtype + ZENTRAImpact.NODE_ID_SEPERATOR + value['id'];
                    var isHidden = hidden.indexOf(graph_id) !== -1;
                    var cssClass = "";

                    if (isHidden) {
                        cssClass = "impact-res-disabled";
                    }

                    var str = '<p class="' + cssClass + '" data-id="' + _.escape(value['id']) + '" data-type="' + _.escape(itemtype) + '">';
                    str += `<img src='${_.escape(value['image'])}'></img>`;
                    str += _.escape(value["name"]);

                    if (isHidden) {
                        str += '<i class="ti ti-eye-off impact-res-hidden"></i>';
                    }

                    str += "</p>";

                    $(ZENTRAImpact.selectors.sideSearchResults).append(str);
                });

                // All data was loaded, hide "More..."
                if (data.total <= ((page + 1) * 20)) {
                    $(ZENTRAImpact.selectors.sideSearchMore).hide();
                } else {
                    $(ZENTRAImpact.selectors.sideSearchMore).show();
                }

                // No results
                if (data.total == 0 && page == 0) {
                    $(ZENTRAImpact.selectors.sideSearchNoResults).show();
                }

                $(ZENTRAImpact.selectors.sideSearchSpinner).hide();
            },
            error: function(){
                alert("error");
            },
        });
    },

    /**
    * Get the list of assets already on the graph
    */
    getUsedAssets: function() {
        // Get used ids for this itemtype
        var used = [];
        ZENTRAImpact.cy.nodes().not(ZENTRAImpact.getHiddenSelector()).forEach(function(node) {
            if (node.isParent()) {
                return;
            }

            var nodeId = node.data('id')
                .split(ZENTRAImpact.NODE_ID_SEPERATOR);
            if (nodeId[0] == ZENTRAImpact.selectedItemtype) {
                used.push(parseInt(nodeId[1]));
            }
        });

        return used;
    },

    /**
    * Taken from cytoscape source, get the real position of the click event on
    * the cytoscape canvas
    *
    * @param   {Number}  clientX
    * @param   {Number}  clientY
    * @param   {Boolean} rendered
    * @returns {Object}
    */
    projectIntoViewport: function (clientX, clientY, rendered) {
        var cy = this.cy;
        var offsets = this.findContainerClientCoords();
        var offsetLeft = offsets[0];
        var offsetTop = offsets[1];
        var scale = offsets[4];
        var pan = cy.pan();
        var zoom = cy.zoom();

        if (rendered) {
            return {
                x: clientX - offsetLeft,
                y: clientY - offsetTop
            };
        } else {
            return {
                x: ((clientX - offsetLeft) / scale - pan.x) / zoom,
                y: ((clientY - offsetTop) / scale - pan.y) / zoom
            };
        }
    },

    /**
    * Used for projectIntoViewport
    *
    * @returns {Array}
    */
    findContainerClientCoords: function () {
        var container = this.impactContainer[0];
        var rect = container.getBoundingClientRect();
        var style = window.getComputedStyle(container);

        var styleValue = function styleValue(name) {
            return parseFloat(style.getPropertyValue(name));
        };

        var padding = {
            left  : styleValue('padding-left'),
            right : styleValue('padding-right'),
            top   : styleValue('padding-top'),
            bottom: styleValue('padding-bottom')
        };
        var border = {
            left  : styleValue('border-left-width'),
            right : styleValue('border-right-width'),
            top   : styleValue('border-top-width'),
            bottom: styleValue('border-bottom-width')
        };
        var clientWidth      = container.clientWidth;
        var clientHeight     = container.clientHeight;
        var paddingHor       = padding.left + padding.right;
        var paddingVer       = padding.top + padding.bottom;
        var borderHor        = border.left + border.right;
        var scale            = rect.width / (clientWidth + borderHor);
        var unscaledW        = clientWidth - paddingHor;
        var unscaledH        = clientHeight - paddingVer;
        var left             = rect.left + padding.left + border.left;
        var top              = rect.top + padding.top + border.top;
        return [left, top, unscaledW, unscaledH, scale];
    },

    /**
    * Set event handler for toolbar events
    */
    initToolbar: function() {
        // Save the graph
        $(ZENTRAImpact.selectors.save).click(function() {
            ZENTRAImpact.showCleanWorkspaceStatus();
            // Send data as JSON on submit
            $.ajax({
                type: "POST",
                url: $(ZENTRAImpact.selectors.form).prop('action'),
                data: {
                    'impacts': JSON.stringify(ZENTRAImpact.computeDelta())
                },
                success: function(){
                    ZENTRAImpact.initialState = ZENTRAImpact.getCurrentState();
                    $(document).trigger('impactUpdated');
                },
                error: function(){
                    ZENTRAImpact.showDirtyWorkspaceStatus();
                },
            });
        });

        // Add a node on the graph
        $(ZENTRAImpact.selectors.addNode).click(function() {
            ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_ADD_NODE);
        });

        // Add a edge on the graph
        $(ZENTRAImpact.selectors.addEdge).click(function() {
            ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_ADD_EDGE);
        });

        // Add a compound on the graph
        $(ZENTRAImpact.selectors.addCompound).click(function() {
            ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_ADD_COMPOUND);
        });

        // Enter delete mode
        $(ZENTRAImpact.selectors.deleteElement).click(function() {
            ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_DELETE);
        });

        // Export graph
        $(ZENTRAImpact.selectors.export).click(function() {
            ZENTRAImpact.download(
                'png',
                false
            );
        });

        // Show settings
        $(this.selectors.impactSettings).click(function() {
            if ($(this).find('i.fa-chevron-right').length) {
                ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_DEFAULT);
            } else {
                ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_SETTINGS);
            }
        });

        $(ZENTRAImpact.selectors.undo).click(function() {
            ZENTRAImpact.undo();
        });

        // Redo button
        $(ZENTRAImpact.selectors.redo).click(function() {
            ZENTRAImpact.redo();
        });

        // Toggle expanded toolbar
        $(this.selectors.sideToggle).click(function() {
            if ($(this).find('i.fa-chevron-right').length) {
                ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_DEFAULT);
            } else {
                ZENTRAImpact.setEditionMode(ZENTRAImpact.EDITION_ADD_NODE);
            }
        });

        // Toggle impact visibility
        $(ZENTRAImpact.selectors.toggleImpact).click(function() {
            ZENTRAImpact.toggleVisibility(ZENTRAImpact.FORWARD);
            ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_EDIT_IMPACT_VISIBILITY, {});
        });

        // Toggle depends visibility
        $(ZENTRAImpact.selectors.toggleDepends).click(function() {
            ZENTRAImpact.toggleVisibility(ZENTRAImpact.BACKWARD);
            ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_EDIT_DEPENDS_VISIBILITY, {});
        });

        // Depth selector
        $(ZENTRAImpact.selectors.maxDepth).on('input', function() {
            var previous = ZENTRAImpact.maxDepth;
            ZENTRAImpact.setDepth($(ZENTRAImpact.selectors.maxDepth).val());
            ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_EDIT_MAX_DEPTH, {
                oldDepth: previous,
                newDepth: ZENTRAImpact.maxDepth,
            });
        });

        $(ZENTRAImpact.selectors.toggleFullscreen).click(function() {
            ZENTRAImpact.toggleFullscreen();
        });

        // Filter available itemtypes
        $(ZENTRAImpact.selectors.sideSearchFilterItemtype).on('input', function() {
            var value = $(ZENTRAImpact.selectors.sideSearchFilterItemtype).val().toLowerCase();

            $(ZENTRAImpact.selectors.sideFilterItem + ' img').each(function() {
                var itemtype = $(this).attr('title').toLowerCase();
                if (value == "" || itemtype.indexOf(value) != -1) {
                    $(this).parent().show();
                } else {
                    $(this).parent().hide();
                }
            });
        });

        // Exit type selection and enter asset search
        $(ZENTRAImpact.selectors.sideFilterItem).click(function() {
            var img = $(this).find('img').eq(0);

            ZENTRAImpact.selectedItemtype = $(img).attr('data-itemtype');
            $(ZENTRAImpact.selectors.sideSearch).show();
            $(ZENTRAImpact.selectors.sideSearch + " img").attr('title', $(img).attr('title'));
            $(ZENTRAImpact.selectors.sideSearch + " img").attr('src', $(img).attr('src'));
            $(ZENTRAImpact.selectors.sideSearch + " > h4 > span").html(_.escape($(img).attr('title')));
            $(ZENTRAImpact.selectors.sideSearchSelectItemtype).hide();

            // Empty search
            ZENTRAImpact.searchAssets(
                ZENTRAImpact.selectedItemtype,
                JSON.stringify(ZENTRAImpact.getUsedAssets()),
                $(ZENTRAImpact.selectors.sideFilterAssets).val(),
                0
            );
        });

        // Exit asset search and return to type selection
        $(ZENTRAImpact.selectors.sideSearch + ' > h4 > i').click(function() {
            $(ZENTRAImpact.selectors.sideSearch).hide();
            $(ZENTRAImpact.selectors.sideSearchSelectItemtype).show();
            $(ZENTRAImpact.selectors.sideSearchResults).html("");
        });

        $(ZENTRAImpact.selectors.sideFilterAssets).on('input', function() {
            // Reset results
            $(ZENTRAImpact.selectors.sideSearchResults).html("");
            $(ZENTRAImpact.selectors.sideSearchMore).hide();
            $(ZENTRAImpact.selectors.sideSearchSpinner).show();
            $(ZENTRAImpact.selectors.sideSearchNoResults).hide();

            searchAssetsDebounced(
                ZENTRAImpact.selectedItemtype,
                JSON.stringify(ZENTRAImpact.getUsedAssets()),
                $(ZENTRAImpact.selectors.sideFilterAssets).val(),
                0
            );
        });

        // Load more results on "More..." click
        $(ZENTRAImpact.selectors.sideSearchMore).on('click', function() {
            ZENTRAImpact.searchAssets(
                ZENTRAImpact.selectedItemtype,
                JSON.stringify(ZENTRAImpact.getUsedAssets()),
                $(ZENTRAImpact.selectors.sideFilterAssets).val(),
                ++ZENTRAImpact.addAssetPage
            );
        });

        // Watch for color changes (depends)
        $(ZENTRAImpact.selectors.dependsColor).change(function(){
            var previous = ZENTRAImpact.edgeColors[ZENTRAImpact.BACKWARD];
            ZENTRAImpact.setEdgeColors({
                backward: $(ZENTRAImpact.selectors.dependsColor).val(),
            });
            ZENTRAImpact.updateStyle();
            ZENTRAImpact.cy.trigger("change");
            ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_EDIT_DEPENDS_COLOR, {
                oldColor: previous,
                newColor: ZENTRAImpact.edgeColors[ZENTRAImpact.BACKWARD]
            });
        });

        // Watch for color changes (impact)
        $(ZENTRAImpact.selectors.impactColor).change(function(){
            var previous = ZENTRAImpact.edgeColors[ZENTRAImpact.FORWARD];
            ZENTRAImpact.setEdgeColors({
                forward: $(ZENTRAImpact.selectors.impactColor).val(),
            });
            ZENTRAImpact.updateStyle();
            ZENTRAImpact.cy.trigger("change");
            ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_EDIT_IMPACT_COLOR, {
                oldColor: previous,
                newColor: ZENTRAImpact.edgeColors[ZENTRAImpact.FORWARD]
            });
        });

        // Watch for color changes (impact and depends)
        $(ZENTRAImpact.selectors.impactAndDependsColor).change(function(){
            var previous = ZENTRAImpact.edgeColors[ZENTRAImpact.BOTH];
            ZENTRAImpact.setEdgeColors({
                both: $(ZENTRAImpact.selectors.impactAndDependsColor).val(),
            });
            ZENTRAImpact.updateStyle();
            ZENTRAImpact.cy.trigger("change");
            ZENTRAImpact.addToUndo(ZENTRAImpact.ACTION_EDIT_IMPACT_AND_DEPENDS_COLOR, {
                oldColor: previous,
                newColor: ZENTRAImpact.edgeColors[ZENTRAImpact.BOTH]
            });
        });

        // Handle drag & drop on add node search result
        $(document).on('mousedown', ZENTRAImpact.selectors.sideSearchResults + ' p', function(e) {
            // Only on left click and not for disabled item
            if (e.which !== 1
            || $(e.target).hasClass('impact-res-disabled')
            || $(e.target).parent().hasClass('impact-res-disabled')) {
                return;
            }

            // Tmp data to be shared with mousedown event
            ZENTRAImpact.eventData.addNodeStart = {
                id  : $(this).attr("data-id"),
                type: $(this).attr("data-type"),
            };

            // Show preview icon at cursor location
            $(ZENTRAImpact.selectors.dropPreview).css({
                left: e.clientX - 24,
                top: e.clientY - 24,
            });
            $(ZENTRAImpact.selectors.dropPreview).attr('src', $(this).find('img').attr('src'));
            $(ZENTRAImpact.selectors.dropPreview).show();

            $("*").css({cursor: "grabbing"});
        });

        // Handle drag & drop on add node search result
        $(document).on('mouseup', function(e) {
            // Middle click on badge, open link in new tab
            if (event.which == 2) {
                ZENTRAImpact.checkBadgeHitboxes(
                    ZENTRAImpact.projectIntoViewport(e.clientX, e.clientY, true),
                    true,
                    true
                );
            }

            if (ZENTRAImpact.eventData.addNodeStart === undefined) {
                return;
            }

            if (e.target.nodeName == "CANVAS") {
            // Add node at event position
                ZENTRAImpact.addNode(
                    ZENTRAImpact.eventData.addNodeStart.id,
                    ZENTRAImpact.eventData.addNodeStart.type,
                    ZENTRAImpact.projectIntoViewport(e.clientX, e.clientY, false)
                );
            }

            $(ZENTRAImpact.selectors.dropPreview).hide();

            // Clear tmp event data
            ZENTRAImpact.eventData.addNodeStart = undefined;
            $("*").css('cursor', "");
        });

        $(document).on('mousemove', function(e) {
            if (ZENTRAImpact.eventData.addNodeStart === undefined) {
                return;
            }

            // Show preview icon at cursor location
            $(ZENTRAImpact.selectors.dropPreview).css({
                left: e.clientX - 24,
                top: e.clientY - 24,
            });
        });
    },

    /**
    * Init and render the canvas overlay used to show the badges
    */
    initCanvasOverlay: function() {
        var layer = ZENTRAImpact.cy.cyCanvas();
        var canvas = layer.getCanvas();
        var ctx = canvas.getContext('2d');

        ZENTRAImpact.cy.on("render cyCanvas.resize", function() {
            layer.resetTransform(ctx);
            layer.clear(ctx);
            ZENTRAImpact.badgesHitboxes = [];

            ZENTRAImpact.cy.filter("node:childless:visible").forEach(function(node) {
            // Stop here if the node has no badge defined
                if (!node.data('badge')) {
                    return;
                }

                const bg_color = window.tinycolor(node.data('badge').color);
                const rgb = bg_color.toRgb();

                // Set badge position (bottom right corner of the node)
                var bbox = node.renderedBoundingBox({
                    includeLabels  : false,
                    includeOverlays: false,
                    includeNodes   : true,
                });
                var pos = {
                    x: bbox.x2 + ZENTRAImpact.cy.zoom(),
                    y: bbox.y2 + ZENTRAImpact.cy.zoom(),
                };

                // Register badge position so it can be clicked
                ZENTRAImpact.badgesHitboxes.push({
                    position: pos,
                    target  : node.data('badge').target,
                    itemtype: node.data('id').split(ZENTRAImpact.NODE_ID_SEPERATOR)[0],
                    id      : node.data('id').split(ZENTRAImpact.NODE_ID_SEPERATOR)[1],
                    id_option: node.data('id_option'),
                });

                // Draw the badge
                ctx.beginPath();
                ctx.arc(pos.x, pos.y, 4 * ZENTRAImpact.cy.zoom(), 0, 2 * Math.PI, false);
                ctx.fillStyle = "rgb(" + rgb.r + ", " + rgb.g + ", " + rgb.b + ")";
                ctx.fill();

                ctx.fillStyle = window.tinycolor.mostReadable(bg_color, window.tinycolor(bg_color).monochromatic(), {
                    includeFallbackColors: true
                }).toHexString();

                // Print number
                ctx.font = 6 * ZENTRAImpact.cy.zoom() + "px sans-serif";
                ctx.fillText(
                    node.data('badge').count,
                    pos.x - (1.95 * ZENTRAImpact.cy.zoom()),
                    pos.y + (2.23 * ZENTRAImpact.cy.zoom())
                );
            });
        });
    }
};
// Explicitly bind to the `window` object for Jest tests
window.ZENTRAImpact = ZENTRAImpact;

var searchAssetsDebounced = _.debounce(window.ZENTRAImpact.searchAssets, 400, false);
