import { useState, useEffect } from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { PROJECT_STATUS_TEXT_MAP, PROJECT_STATUS_CLASS_MAP } from "@/constants";
import { Head, Link } from "@inertiajs/react";
import TasksTable from "../Task/TasksTable";

export default function Show({ auth, success, project = {}, tasks = [], queryParams = {} }) { 
  const [imageData, setImageData] = useState(null);

  useEffect(() => {
    const fetchImageData = async () => {
      if (project.image_url) {
        try {
          const response = await fetch(project.image_url);
          if (response.ok) {
            const data = await response.json();
            // Extract base64 content from the JSON response
            if (data.content) {
              setImageData(`data:${data.contentType};base64,${data.content}`);
            } else {
              console.error('No image content found in response');
            }
          } else {
            console.error('Failed to fetch image data:', response.statusText);
          }
        } catch (error) {
          console.error('Error fetching image data:', error);
        }
      }
    };

    fetchImageData();
  }, [project.image_url]);

  const getClassName = (status) => PROJECT_STATUS_CLASS_MAP[status] || "bg-gray-500";

  return (
    <AuthenticatedLayout
      user={auth.user}
      header={
        <div className="flex items-center justify-between">
          <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {`Project "${project.name || 'Unknown'}"`}
          </h2>
          <Link
            href={route("project.edit", project.id)}
            className="bg-emerald-500 py-1 px-3 text-white rounded shadow transition-all hover:bg-emerald-600"
          >
            Edit
          </Link>
        </div>
      }
    >
      <Head title={`Project "${project.name || 'Unknown'}"`} />

      <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div>
              {imageData && (
                <img
                  src={imageData}
                  alt="Project Image"
                  className="w-full h-64 object-cover"
                />
              )}
            </div>
            <div className="p-6 text-gray-900 dark:text-gray-100">
              <div className="grid gap-1 grid-cols-2 mt-2">
                <div>
                  <div className="mt-4">
                    <label className="font-bold text-lg">Project ID</label>
                    <p className="mt-1">{project.id || 'N/A'}</p>
                  </div>
                  <div className="mt-4">
                    <label className="font-bold text-lg">Project Name</label>
                    <p className="mt-1">{project.name || 'N/A'}</p>
                  </div>
                  <div className="mt-4">
                    <label className="font-bold text-lg">Project Status</label>
                    <p className="mt-1">
                      <span
                        className={`px-2 py-1 rounded text-white ${getClassName(project.status)}`}
                      >
                        {PROJECT_STATUS_TEXT_MAP[project.status] || 'Unknown Status'}
                      </span>
                    </p>
                  </div>
                  <div className="mt-4">
                    <label className="font-bold text-lg">Created By</label>
                    <p className="mt-1">{project.createdBy?.name || 'N/A'}</p>
                  </div>
                </div>
                <div>
                  <div className="mt-4">
                    <label className="font-bold text-lg">Due Date</label>
                    <p className="mt-1">{project.due_date || 'N/A'}</p>
                  </div>
                  <div className="mt-4">
                    <label className="font-bold text-lg">Create Date</label>
                    <p className="mt-1">{project.created_at || 'N/A'}</p>
                  </div>
                  <div className="mt-4">
                    <label className="font-bold text-lg">Updated By</label>
                    <p className="mt-1">{project.updatedBy?.name || 'N/A'}</p>
                  </div>
                </div>
              </div>
              <div className="mt-4">
                <label className="font-bold text-lg">Project Description</label>
                <p className="mt-1">{project.description || 'N/A'}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="pb-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div className="p-6 text-gray-900 dark:text-gray-100">
              <TasksTable tasks={tasks} success={success} queryParams={queryParams} hideProjectColumn={true}/>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  );
}
