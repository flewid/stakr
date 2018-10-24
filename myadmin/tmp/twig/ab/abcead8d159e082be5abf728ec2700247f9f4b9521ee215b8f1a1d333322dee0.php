<?php

/* display/export/template_loading.twig */
class __TwigTemplate_d840f39d8046e4b5edd885efa8646804bc0744c43393ce98616f3797f2efd56a extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<div class=\"exportoptions\" id=\"export_templates\">
    <h3>";
        // line 2
        echo _gettext("Export templates:");
        echo "</h3>

    <div class=\"floatleft\">
        <form method=\"post\" action=\"tbl_export.php\" id=\"newTemplateForm\" class=\"ajax\">
            <h4>";
        // line 6
        echo _gettext("New template:");
        echo "</h4>
            <input type=\"text\" name=\"templateName\" id=\"templateName\"
                maxlength=\"64\" placeholder=\"";
        // line 8
        echo _gettext("Template name");
        echo "\" required>
            <input type=\"submit\" name=\"createTemplate\" id=\"createTemplate\"
                value=\"";
        // line 10
        echo _gettext("Create");
        echo "\">
        </form>
    </div>

    <div class=\"floatleft\" style=\"margin-left: 50px;\">
        <form method=\"post\" action=\"tbl_export.php\" id=\"existingTemplatesForm\" class=\"ajax\">
            <h4>";
        // line 16
        echo _gettext("Existing templates:");
        echo "</h4>
            <label for=\"template\">";
        // line 17
        echo _gettext("Template:");
        echo "</label>
            <select name=\"template\" id=\"template\" required>
                ";
        // line 19
        echo ($context["options"] ?? null);
        echo "
            </select>
            <input type=\"submit\" name=\"updateTemplate\" id=\"updateTemplate\" value=\"";
        // line 21
        echo _gettext("Update");
        echo "\">
            <input type=\"submit\" name=\"deleteTemplate\" id=\"deleteTemplate\" value=\"";
        // line 22
        echo _gettext("Delete");
        echo "\">
        </form>
    </div>

    <div class=\"clearfloat\"></div>
</div>
";
    }

    public function getTemplateName()
    {
        return "display/export/template_loading.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  66 => 22,  62 => 21,  57 => 19,  52 => 17,  48 => 16,  39 => 10,  34 => 8,  29 => 6,  22 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "display/export/template_loading.twig", "/home/flewid/public_html/scribble.flewid.ca/htdocs/myadmin/templates/display/export/template_loading.twig");
    }
}
