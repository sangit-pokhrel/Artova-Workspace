<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/data/v1beta/data.proto

namespace Google\Analytics\Data\V1beta;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The quantitative measurements of a report. For example, the metric
 * `eventCount` is the total number of events. Requests are allowed up to 10
 * metrics.
 *
 * Generated from protobuf message <code>google.analytics.data.v1beta.Metric</code>
 */
class Metric extends \Google\Protobuf\Internal\Message
{
    /**
     * The name of the metric. See the [API
     * Metrics](https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema#metrics)
     * for the list of metric names.
     * If `expression` is specified, `name` can be any string that you would like
     * within the allowed character set. For example if `expression` is
     * `screenPageViews/sessions`, you could call that metric's name =
     * `viewsPerSession`. Metric names that you choose must match the regular
     * expression "^[a-zA-Z0-9_]$".
     * Metrics are referenced by `name` in `metricFilter`, `orderBys`, and metric
     * `expression`.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     */
    private $name = '';
    /**
     * A mathematical expression for derived metrics. For example, the metric
     * Event count per user is `eventCount/totalUsers`.
     *
     * Generated from protobuf field <code>string expression = 2;</code>
     */
    private $expression = '';
    /**
     * Indicates if a metric is invisible in the report response. If a metric is
     * invisible, the metric will not produce a column in the response, but can be
     * used in `metricFilter`, `orderBys`, or a metric `expression`.
     *
     * Generated from protobuf field <code>bool invisible = 3;</code>
     */
    private $invisible = false;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $name
     *           The name of the metric. See the [API
     *           Metrics](https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema#metrics)
     *           for the list of metric names.
     *           If `expression` is specified, `name` can be any string that you would like
     *           within the allowed character set. For example if `expression` is
     *           `screenPageViews/sessions`, you could call that metric's name =
     *           `viewsPerSession`. Metric names that you choose must match the regular
     *           expression "^[a-zA-Z0-9_]$".
     *           Metrics are referenced by `name` in `metricFilter`, `orderBys`, and metric
     *           `expression`.
     *     @type string $expression
     *           A mathematical expression for derived metrics. For example, the metric
     *           Event count per user is `eventCount/totalUsers`.
     *     @type bool $invisible
     *           Indicates if a metric is invisible in the report response. If a metric is
     *           invisible, the metric will not produce a column in the response, but can be
     *           used in `metricFilter`, `orderBys`, or a metric `expression`.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Analytics\Data\V1Beta\Data::initOnce();
        parent::__construct($data);
    }

    /**
     * The name of the metric. See the [API
     * Metrics](https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema#metrics)
     * for the list of metric names.
     * If `expression` is specified, `name` can be any string that you would like
     * within the allowed character set. For example if `expression` is
     * `screenPageViews/sessions`, you could call that metric's name =
     * `viewsPerSession`. Metric names that you choose must match the regular
     * expression "^[a-zA-Z0-9_]$".
     * Metrics are referenced by `name` in `metricFilter`, `orderBys`, and metric
     * `expression`.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The name of the metric. See the [API
     * Metrics](https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema#metrics)
     * for the list of metric names.
     * If `expression` is specified, `name` can be any string that you would like
     * within the allowed character set. For example if `expression` is
     * `screenPageViews/sessions`, you could call that metric's name =
     * `viewsPerSession`. Metric names that you choose must match the regular
     * expression "^[a-zA-Z0-9_]$".
     * Metrics are referenced by `name` in `metricFilter`, `orderBys`, and metric
     * `expression`.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;

        return $this;
    }

    /**
     * A mathematical expression for derived metrics. For example, the metric
     * Event count per user is `eventCount/totalUsers`.
     *
     * Generated from protobuf field <code>string expression = 2;</code>
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * A mathematical expression for derived metrics. For example, the metric
     * Event count per user is `eventCount/totalUsers`.
     *
     * Generated from protobuf field <code>string expression = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setExpression($var)
    {
        GPBUtil::checkString($var, True);
        $this->expression = $var;

        return $this;
    }

    /**
     * Indicates if a metric is invisible in the report response. If a metric is
     * invisible, the metric will not produce a column in the response, but can be
     * used in `metricFilter`, `orderBys`, or a metric `expression`.
     *
     * Generated from protobuf field <code>bool invisible = 3;</code>
     * @return bool
     */
    public function getInvisible()
    {
        return $this->invisible;
    }

    /**
     * Indicates if a metric is invisible in the report response. If a metric is
     * invisible, the metric will not produce a column in the response, but can be
     * used in `metricFilter`, `orderBys`, or a metric `expression`.
     *
     * Generated from protobuf field <code>bool invisible = 3;</code>
     * @param bool $var
     * @return $this
     */
    public function setInvisible($var)
    {
        GPBUtil::checkBool($var);
        $this->invisible = $var;

        return $this;
    }

}

