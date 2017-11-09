<?php
namespace MinicoSilverBundle\Datatables;

use \Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;

class PostDatatable extends AbstractDatatableView
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatableView()
    {
        //-------------------------------------------------
        // Datatable
        //-------------------------------------------------

        // Features (defaults)
        $this->getFeatures()
            ->setAutoWidth(true)
            ->setDeferRender(false)
            ->setInfo(true)
            ->setJQueryUI(false)
            ->setLengthChange(true)
            ->setOrdering(true)
            ->setPaging(true)
            ->setProcessing(true)  // default: false
            ->setScrollX(true)     // default: false
            ->setScrollY("")
            ->setSearching(true)
            ->setServerSide(true)  // default: false
            ->setStateSave(false)
            ->setDelay(500);       // default: 0

        // Options (for more options see file: Sg\DatatablesBundle\Datatable\View\Options.php)
        //$this->getOptions()->setLengthMenu(array(10, 25, 50));
        $this->getOptions()
            ->setLengthMenu(array(10, 25, 50, 100, -1))
            ->setOrder(array("column" => 1, "direction" => "desc"));

        $this->getAjax()->setUrl($this->getRouter()->generate("post_results"));

        $this->getMultiselect()
            ->setEnabled(true)
            ->setPosition("last")
            ->addAction("Hide post", "post_bulk_disable")
            ->addAction("Delete post", "post_bulk_delete")
            ->setAttributes(array(
                "class" => "testclass123"
            ))
            ->setClassName("multi-test-class")
            ->setWidth("90px");

        $this->setStyle(self::BOOTSTRAP_3_STYLE);

        $this->setIndividualFiltering(true);


        //-------------------------------------------------
        // Columns
        //-------------------------------------------------

        $this->getColumnBuilder()
            ->add("id", "column", array(
                "title" => "Post-id"
//                "searchable" => true,
//                "orderable" => true,
//                "visible" => true,
//                "class" => "active",
//                "width" => "100px"
            ))
            ->add("name", "column", array(
                "title" => "Castegory"
            ));
//            ->add("updatedBy.username", "column", array(
//                "title" => "Updated by"
//            ))
//            ->add("title", "column", array(
//                "title" => $this->getTranslator()->trans("test.title", array(), "msg")
//            ))
//            ->add("visible", "boolean", array(
//                "title" => "Visible",
//                "true_label" => "yes",
//                "false_label" => "no",
//                "true_icon" => "glyphicon glyphicon-ok",
//                "false_icon" => "glyphicon glyphicon-remove"
//            ))
//            ->add("createdAt", /*choose timeago or datetime*/ "datetime", array(
//                "title" => "Created at"
//            ))
//            ->add("tags.name", "array", array(
//                "title" => "Tags",
//                "read_as" => "tags[, ].name"
//            ))
//            ->add(null, "action", array(
//                "title" => "Actions",
//                "start" => '<div class="wrapper">',
//                "end" => '</div>',
//                "actions" => array(
//                    array(
//                        "route" => "post_edit",
//                        "route_parameters" => array(
//                            "id" => "id"
//                        ),
//                        "icon" => "glyphicon glyphicon-edit",
//                        "attributes" => array(
//                            "rel" => "tooltip",
//                            "title" => "Edit",
//                            "class" => "btn btn-primary btn-xs",
//                            "role" => "button"
//                        ),
//                        "confirm" => true,
//                        "confirm_message" => "Are you sure?",
//                        "role" => "ROLE_ADMIN",
//                        "renderif" => array(
//                            "visible"
//                        )
//                    ),
//                    array(
//                        "route" => "post_show",
//                        "route_parameters" => array(
//                            "id" => "id"
//                        ),
//                        "label" => "Show",
//                        "attributes" => array(
//                            "rel" => "tooltip",
//                            "title" => "Show",
//                            "class" => "btn btn-default btn-xs",
//                            "role" => "button"
//                        ),
//                        //"role" => "ROLE_USER",
//                        //"renderif" => array("visible")
//                    )
//                )
//            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return "MinicoSilverBundle:Category";
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "post_datatable";
    }
}
