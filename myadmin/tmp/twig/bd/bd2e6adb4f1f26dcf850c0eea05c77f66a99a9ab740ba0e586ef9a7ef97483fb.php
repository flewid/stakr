<?php

/* privileges/add_privileges_routine.twig */
class __TwigTemplate_0ec849657ad0fa5d003a3793bdce3f6f3dcea4276bddd2c6f5afd356e9ae9bc7 extends Twig_Template
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

<label for=\"text_routinename\">";
        // line 3
        echo _gettext("Add privileges on the following routine:");
        echo "</label>";
        // line 5
        if ( !twig_test_empty(($context["routines"] ?? null))) {
            // line 6
            echo "    <select name=\"pred_routinename\" class=\"autosubmit\">
        <option value=\"\" selected=\"selected\">";
            // line 7
            echo _gettext("Use text field");
            echo ":</option>
        ";
            // line 8
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["routines"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["routine"]) {
                // line 9
                echo "            <option value=\"";
                echo twig_escape_filter($this->env, $context["routine"], "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $context["routine"], "html", null, true);
                echo "</option>
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['routine'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 11
            echo "    </select>
";
        }
        // line 14
        echo "<input type=\"text\" id=\"text_routinename\" name=\"routinename\" />
";
    }

    public function getTemplateName()
    {
        return "privileges/add_privileges_routine.twig";
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
        return new Twig_Source("", "privileges/add_privileges_routine.twig", "/home/flewid/public_html/scribble.flewid.ca/htdocs/myadmin/templates/privileges/add_privileges_routine.twig");
    }
}
