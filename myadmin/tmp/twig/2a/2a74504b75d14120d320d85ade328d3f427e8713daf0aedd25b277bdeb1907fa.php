<?php

/* privileges/add_privileges_table.twig */
class __TwigTemplate_1b9d95e6e68359b1fc1aae4bc473327a92099ba44bb768216a024bacf90ee322 extends Twig_Template
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
        echo "<input type=\"hidden\" name=\"dbname\" value=\"";
        echo twig_escape_filter($this->env, ($context["database"] ?? null), "html", null, true);
        echo "\"/>

<label for=\"text_tablename\">";
        // line 3
        echo _gettext("Add privileges on the following table:");
        echo "</label>";
        // line 5
        if ( !twig_test_empty(($context["tables"] ?? null))) {
            // line 6
            echo "    <select name=\"pred_tablename\" class=\"autosubmit\">
        <option value=\"\" selected=\"selected\">";
            // line 7
            echo _gettext("Use text field");
            echo ":</option>
        ";
            // line 8
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["tables"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["table"]) {
                // line 9
                echo "            <option value=\"";
                echo twig_escape_filter($this->env, $context["table"], "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $context["table"], "html", null, true);
                echo "</option>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['table'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 11
            echo "    </select>
";
        }
        // line 14
        echo "<input type=\"text\" id=\"text_tablename\" name=\"tablename\" />
";
    }

    public function getTemplateName()
    {
        return "privileges/add_privileges_table.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  56 => 14,  52 => 11,  41 => 9,  37 => 8,  33 => 7,  30 => 6,  28 => 5,  25 => 3,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "privileges/add_privileges_table.twig", "/home/flewid/public_html/scribble.flewid.ca/htdocs/myadmin/templates/privileges/add_privileges_table.twig");
    }
}
