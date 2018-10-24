<?php

/* privileges/initials_row.twig */
class __TwigTemplate_dde8c1f2e48ac5a4d19e3edfba4b08f957e4a46a1182e56cbdd14de5cfa82d10 extends Twig_Template
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
        echo "<table id=\"initials_table\" cellspacing=\"5\">
    <tr>
        ";
        // line 3
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["array_initials"] ?? null));
        foreach ($context['_seq'] as $context["tmp_initial"] => $context["initial_was_found"]) {
            if ( !($context["tmp_initial"] === null)) {
                // line 4
                echo "            ";
                if ($context["initial_was_found"]) {
                    // line 5
                    echo "                <td>
                    <a class=\"ajax";
                    // line 7
                    echo ((((isset($context["initial"]) || array_key_exists("initial", $context)) && (($context["initial"] ?? null) === $context["tmp_initial"]))) ? (" active") : (""));
                    // line 8
                    echo "\" href=\"server_privileges.php";
                    // line 9
                    echo PhpMyAdmin\Url::getCommon(array("initial" => $context["tmp_initial"]));
                    echo "\">";
                    // line 10
                    echo $context["tmp_initial"];
                    // line 11
                    echo "</a>
                </td>
            ";
                } else {
                    // line 14
                    echo "                <td>";
                    echo $context["tmp_initial"];
                    echo "</td>
            ";
                }
                // line 16
                echo "        ";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['tmp_initial'], $context['initial_was_found'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 17
        echo "        <td>
            <a href=\"server_privileges.php";
        // line 19
        echo PhpMyAdmin\Url::getCommon(array("showall" => 1));
        echo "\" class=\"nowrap\">
                ";
        // line 20
        echo _gettext("Show all");
        // line 21
        echo "            </a>
        </td>
    </tr>
</table>
";
    }

    public function getTemplateName()
    {
        return "privileges/initials_row.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  70 => 21,  68 => 20,  64 => 19,  61 => 17,  54 => 16,  48 => 14,  43 => 11,  41 => 10,  38 => 9,  36 => 8,  34 => 7,  31 => 5,  28 => 4,  23 => 3,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "privileges/initials_row.twig", "/home/flewid/public_html/scribble.flewid.ca/htdocs/myadmin/templates/privileges/initials_row.twig");
    }
}
